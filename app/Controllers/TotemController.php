<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\View;
use App\Models\Setting;
use App\Models\Room;
use App\Models\RoomReservation;
use App\Models\RoomItem;
use App\Models\Seller;
use App\Models\Unit;
use App\Models\ActivityLog;
use App\Models\ReservationAudit;
use App\Services\NotificationService;

/**
 * Modo Totem - acesso por PIN, apenas consulta e reserva de salas.
 * Não requer login de usuário. Não expõe nenhuma configuração administrativa.
 */
class TotemController extends Controller
{
    private Setting $settingModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    /**
     * Exige que o modo Totem esteja habilitado nas configurações.
     */
    private function requireTotemEnabled(): void
    {
        if (!$this->settingModel->getValue('totem_enabled', false)) {
            Session::flash('error', 'O modo Totem está desativado.');
            $this->redirect('/login');
            exit;
        }
    }

    /**
     * Verifica se o acesso ao totem já foi liberado via PIN e retorna a unidade.
     */
    private function requireTotemSession(): object
    {
        $this->requireTotemEnabled();
        if (!Session::has('totem_access') || !Session::has('totem_unit_id')) {
            $this->redirect('/totem/pin');
            exit;
        }

        $unitModel = new Unit();
        $unit = $unitModel->find((int) Session::get('totem_unit_id'));
        if (!$unit || (int) $unit->active !== 1) {
            // Unidade removida/desativada: encerra sessão do totem
            Session::remove('totem_access');
            Session::remove('totem_unit_id');
            Session::flash('error', 'Unidade indisponível. Faça o acesso novamente.');
            $this->redirect('/totem/pin');
            exit;
        }
        return $unit;
    }

    /**
     * Tela de digitação do PIN.
     */
    public function pin(): void
    {
        $this->requireTotemEnabled();

        // Só redireciona ao dashboard se a sessão estiver completa (acesso + unidade válida).
        // Caso contrário, limpa qualquer estado órfão para evitar loop de redirecionamento.
        if (Session::has('totem_access') && Session::has('totem_unit_id')) {
            $unit = (new Unit())->find((int) Session::get('totem_unit_id'));
            if ($unit && (int) $unit->active === 1) {
                $this->redirect('/totem');
                return;
            }
        }
        Session::remove('totem_access');
        Session::remove('totem_unit_id');

        View::render('totem/pin', [
            'logo' => (string) $this->settingModel->getValue('totem_logo', ''),
        ], 'totem');
    }

    /**
     * Valida o PIN informado.
     */
    public function verifyPin(): void
    {
        $this->requireTotemEnabled();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/totem/pin');
            return;
        }

        $pin = trim((string) $this->input('pin', ''));

        // Identifica a unidade pelo PIN (cada unidade tem um PIN exclusivo)
        $unitModel = new Unit();
        $unit = $pin !== '' ? $unitModel->findByPin($pin) : null;

        if ($unit) {
            Session::set('totem_access', true);
            Session::set('totem_unit_id', (int) $unit->id);
            $log = new ActivityLog();
            $log->log('totem.access', 'unit', (int) $unit->id, "Acesso ao Totem — unidade {$unit->name}");
            $this->redirect('/totem');
            return;
        }

        Session::flash('error', 'PIN incorreto. Tente novamente.');
        $this->redirect('/totem/pin');
    }

    /**
     * Encerra a sessão do totem (volta para a tela de PIN).
     */
    public function exit(): void
    {
        Session::remove('totem_access');
        Session::remove('totem_unit_id');
        $this->redirect('/totem/pin');
    }

    /**
     * Tela principal do totem (dashboard de salas).
     */
    public function index(): void
    {
        $unit = $this->requireTotemSession();
        $sellerModel = new Seller();

        View::render('totem/index', [
            'config'  => $this->totemConfig($unit),
            'sellers' => $sellerModel->getActive(),
            'logo'    => (string) $this->settingModel->getValue('totem_logo', ''),
            'unit'    => $unit,
        ], 'totem');
    }

    /**
     * Retorna o estado atual das salas em JSON (para atualização em tempo real).
     */
    public function rooms(): void
    {
        $unit = $this->requireTotemSession();
        $config = $this->totemConfig($unit);

        $today = date('Y-m-d');
        // Data selecionada (padrão: hoje). Não permite datas passadas.
        $date = (string) $this->query('date', $today);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < $today) {
            $date = $today;
        }
        $isToday = ($date === $today);
        $now  = $isToday ? date('H:i:s') : '00:00:00';

        $roomModel = new Room();
        $reservationModel = new RoomReservation();
        $itemModel = new RoomItem();
        // Somente as salas da unidade deste totem
        $rooms = $roomModel->getTotemRooms((int) $unit->id);

        $data = [];
        foreach ($rooms as $room) {
            $reservations = $reservationModel->getByRoomAndDate((int) $room->id, $date);
            $current = $reservationModel->getCurrent((int) $room->id, $date, $now);
            $next    = $reservationModel->getNext((int) $room->id, $date, $now);
            $items   = $itemModel->getActiveByRoom((int) $room->id);

            // Status visual
            $status = 'available';
            if ($current) {
                $status = 'occupied';
            } elseif ($next && strtotime($next->start_time) - strtotime($now) <= 30 * 60) {
                $status = 'soon'; // reservada em breve (próximos 30 min)
            }

            $data[] = [
                'id'          => (int) $room->id,
                'name'        => $room->name,
                'icon'        => $room->icon,
                'image'       => $room->image_path ?? null,
                'capacity'    => $room->capacity,
                'description' => $room->description,
                'status'      => $status,
                'items'       => array_map(fn($it) => [
                    'name'        => $it->name,
                    'description' => $it->description,
                    'icon'        => $it->icon,
                ], $items),
                'current'     => $current ? [
                    'start' => substr($current->start_time, 0, 5),
                    'end'   => substr($current->end_time, 0, 5),
                    'name'  => $current->customer_name,
                ] : null,
                'next'        => $next ? [
                    'start' => substr($next->start_time, 0, 5),
                    'end'   => substr($next->end_time, 0, 5),
                ] : null,
                'reservations' => array_map(fn($r) => [
                    'start' => substr($r->start_time, 0, 5),
                    'end'   => substr($r->end_time, 0, 5),
                    'name'  => $r->customer_name,
                ], $reservations),
                'slots'       => $this->buildSlots($reservations, $isToday, $config),
            ];
        }

        $this->json([
            'date'       => $date,
            'today'      => $today,
            'is_today'   => $isToday,
            'now'        => substr($now, 0, 5),
            'unit'       => ['id' => (int) $unit->id, 'name' => $unit->name, 'location' => $unit->location],
            'rooms'      => $data,
            'refresh_ms' => ((int) $this->settingModel->getValue('totem_refresh_seconds', 15)) * 1000,
        ]);
    }

    /**
     * Registra uma nova reserva feita pelo totem.
     */
    public function reserve(): void
    {
        $unit = $this->requireTotemSession();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Token de segurança inválido.'], 400);
            return;
        }

        $roomId    = (int) $this->input('room_id', 0);
        $startTime = trim((string) $this->input('start_time', ''));
        $duration  = (int) $this->input('duration', 0);
        $name      = trim((string) $this->input('customer_name', ''));
        $phone     = trim((string) $this->input('customer_phone', ''));
        $email     = trim((string) $this->input('customer_email', ''));
        $sellerId  = (int) $this->input('seller_id', 0);
        $interest  = trim((string) $this->input('interest', ''));
        $date      = trim((string) $this->input('date', date('Y-m-d')));

        $config = $this->totemConfig($unit);

        // Valida a data (não permite datas passadas)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < date('Y-m-d')) {
            $this->json(['success' => false, 'message' => 'Data inválida.'], 422);
            return;
        }

        // Validações básicas
        if ($roomId <= 0 || $startTime === '' || $name === '') {
            $this->json(['success' => false, 'message' => 'Preencha o nome e escolha um horário.'], 422);
            return;
        }
        if ($config['require_email'] && $email === '') {
            $this->json(['success' => false, 'message' => 'O e-mail é obrigatório.'], 422);
            return;
        }

        // Duração: usa padrão se não informada; valida limites e alinhamento ao intervalo
        if ($duration <= 0) {
            $duration = $config['default_duration'];
        }
        if ($duration < $config['min_duration'] || $duration > $config['max_duration']) {
            $this->json(['success' => false, 'message' => 'Duração fora do permitido (' . $config['min_duration'] . '–' . $config['max_duration'] . ' min).'], 422);
            return;
        }
        if ($duration % $config['slot_minutes'] !== 0) {
            $this->json(['success' => false, 'message' => 'Duração inválida para o intervalo configurado.'], 422);
            return;
        }

        // Confirma que a sala existe, está disponível e pertence à unidade do totem
        $roomModel = new Room();
        $room = $roomModel->find($roomId);
        if (!$room || (int) $room->active !== 1 || (int) $room->show_in_totem !== 1 || (int) $room->unit_id !== (int) $unit->id) {
            $this->json(['success' => false, 'message' => 'Sala indisponível.'], 404);
            return;
        }

        $endTime = date('H:i:s', strtotime($startTime) + $duration * 60);
        $startTime = date('H:i:s', strtotime($startTime));

        // Dentro do horário de funcionamento
        if ($startTime < $config['open_time'] . ':00' || $endTime > $config['close_time'] . ':00') {
            $this->json(['success' => false, 'message' => 'Horário fora do funcionamento.'], 422);
            return;
        }

        // Antecedência mínima e não permitir horário passado (só se for hoje)
        if ($date === date('Y-m-d')) {
            $slotStart = strtotime($date . ' ' . $startTime);
            $minStart  = time() + $config['advance_minutes'] * 60;
            if ($slotStart < $minStart) {
                $this->json(['success' => false, 'message' => 'Horário indisponível para reserva.'], 422);
                return;
            }
        }

        // Vendedor selecionado (opcional, mas com dropdown de cadastros)
        $seller = null;
        if ($sellerId > 0) {
            $sellerModel = new Seller();
            $seller = $sellerModel->find($sellerId);
            if (!$seller || (int) $seller->active !== 1) {
                $this->json(['success' => false, 'message' => 'Vendedor inválido.'], 422);
                return;
            }
        }

        $reservationModel = new RoomReservation();

        // Aplica buffer: expande a janela verificada para garantir folga entre reservas
        $buffer = $config['buffer_minutes'];
        $checkStart = date('H:i:s', strtotime($startTime) - $buffer * 60);
        $checkEnd   = date('H:i:s', strtotime($endTime) + $buffer * 60);
        if ($reservationModel->hasConflict($roomId, $date, $checkStart, $checkEnd)) {
            $msg = $buffer > 0
                ? "Este horário conflita com outra reserva (respeitando {$buffer} min de intervalo)."
                : 'Este horário acabou de ser reservado.';
            $this->json(['success' => false, 'message' => $msg], 409);
            return;
        }

        $id = $reservationModel->create([
            'room_id'          => $roomId,
            'reservation_date' => $date,
            'start_time'       => $startTime,
            'end_time'         => $endTime,
            'customer_name'    => $name,
            'customer_phone'   => $phone !== '' ? $phone : null,
            'customer_email'   => $email !== '' ? $email : null,
            'seller_id'        => $seller ? (int) $seller->id : null,
            'seller_name'      => $seller ? $seller->name : null,
            'interest'         => $interest !== '' ? $interest : null,
            'status'           => 'reserved',
            'source'           => 'totem',
        ]);

        $log = new ActivityLog();
        $log->log('totem.reserve', 'room_reservation', $id, "Reserva no totem: {$room->name} {$name}");

        // Auditoria da criação
        (new ReservationAudit())->record($id, 'created', 'room', null, $room->name, 'Totem');

        // Itens da sala para compor o briefing da notificação
        $itemModel = new RoomItem();
        $items = array_map(fn($it) => [
            'name'        => $it->name,
            'description' => $it->description,
        ], $itemModel->getActiveByRoom($roomId));

        $notificationData = [
            'reservation_id' => $id,
            'room'           => $room->name,
            'date'           => date('d/m/Y', strtotime($date)),
            'start'          => substr($startTime, 0, 5),
            'end'            => substr($endTime, 0, 5),
            'customer_name'  => $name,
            'customer_phone' => $phone,
            'customer_email' => $email,
            'seller_id'      => $seller ? (int) $seller->id : null,
            'seller_name'    => $seller ? $seller->name : null,
            'seller_email'   => $seller ? ($seller->email ?? null) : null,
            'seller_phone'   => $seller ? ($seller->phone ?? null) : null,
            'interest'       => $interest,
            'items'          => $items,
        ];

        // Notificações (e-mail + webhook) de forma síncrona — mesmo caminho do
        // teste de SMTP, que já funciona. Falhas são registradas no log e não
        // impedem a confirmação (cada envio tem timeout curto).
        try {
            $notifier = new NotificationService();
            $notifier->notifyReservation($notificationData);
        } catch (\Throwable $e) {
            error_log('[TotemController] Falha ao notificar reserva: ' . $e->getMessage());
        }

        $this->json([
            'success' => true,
            'message' => 'Reserva confirmada!',
            'reservation' => [
                'room'  => $room->name,
                'start' => substr($startTime, 0, 5),
                'end'   => substr($endTime, 0, 5),
            ],
        ]);
    }

    /**
     * Retorna os detalhes de uma reserva, protegido por PIN.
     * Usado para ver quem reservou antes de editar.
     */
    public function reservationDetail(): void
    {
        $this->requireTotemSession();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Token de segurança inválido.'], 400);
            return;
        }

        $pin = trim((string) $this->input('pin', ''));
        $expected = (string) $this->settingModel->getValue('totem_pin', '');
        if ($pin === '' || !hash_equals($expected, $pin)) {
            $this->json(['success' => false, 'message' => 'PIN incorreto.'], 403);
            return;
        }

        $id = (int) $this->input('reservation_id', 0);
        $reservationModel = new RoomReservation();
        $r = $reservationModel->find($id);
        if (!$r || $r->status === 'cancelled') {
            $this->json(['success' => false, 'message' => 'Reserva não encontrada.'], 404);
            return;
        }

        $this->json([
            'success' => true,
            'reservation' => [
                'id'             => (int) $r->id,
                'customer_name'  => $r->customer_name,
                'customer_phone' => $r->customer_phone,
                'customer_email' => $r->customer_email,
                'seller_id'      => $r->seller_id ? (int) $r->seller_id : null,
                'seller_name'    => $r->seller_name,
                'interest'       => $r->interest ?? null,
                'start'          => substr($r->start_time, 0, 5),
                'end'            => substr($r->end_time, 0, 5),
            ],
        ]);
    }

    /**
     * Atualiza os dados de uma reserva existente (protegido por PIN).
     * Permite ajustar visitante, contato e vendedor. Não move o horário.
     */
    public function updateReservation(): void
    {
        $this->requireTotemSession();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Token de segurança inválido.'], 400);
            return;
        }

        $pin = trim((string) $this->input('pin', ''));
        $expected = (string) $this->settingModel->getValue('totem_pin', '');
        if ($pin === '' || !hash_equals($expected, $pin)) {
            $this->json(['success' => false, 'message' => 'PIN incorreto.'], 403);
            return;
        }

        $id = (int) $this->input('reservation_id', 0);
        $reservationModel = new RoomReservation();
        $r = $reservationModel->find($id);
        if (!$r || $r->status === 'cancelled') {
            $this->json(['success' => false, 'message' => 'Reserva não encontrada.'], 404);
            return;
        }

        // Cancelamento
        if ($this->input('action', '') === 'cancel') {
            $reservationModel->update($id, ['status' => 'cancelled']);
            $log = new ActivityLog();
            $log->log('totem.reservation_cancelled', 'room_reservation', $id, "Reserva cancelada no totem");

            // Auditoria do cancelamento
            (new ReservationAudit())->record($id, 'cancelled', 'status', 'reserved', 'cancelled', 'Totem');

            // Notifica cancelamento (usa os dados atuais da reserva)
            try {
                $notifier = new NotificationService();
                $notifier->notifyReservation($this->buildReservationNotificationData($r, 'cancelled'), 'cancelled');
            } catch (\Throwable $e) {
                error_log('[TotemController] Falha ao notificar cancelamento: ' . $e->getMessage());
            }

            $this->json(['success' => true, 'message' => 'Reserva cancelada.', 'cancelled' => true]);
            return;
        }

        $name  = trim((string) $this->input('customer_name', ''));
        $phone = trim((string) $this->input('customer_phone', ''));
        $email = trim((string) $this->input('customer_email', ''));
        $interest = trim((string) $this->input('interest', ''));
        $sellerId = (int) $this->input('seller_id', 0);

        if ($name === '') {
            $this->json(['success' => false, 'message' => 'O nome é obrigatório.'], 422);
            return;
        }

        $seller = null;
        if ($sellerId > 0) {
            $sellerModel = new Seller();
            $seller = $sellerModel->find($sellerId);
        }

        // Estado anterior (para auditoria campo a campo)
        $before = [
            'customer_name'  => (string) ($r->customer_name ?? ''),
            'customer_phone' => (string) ($r->customer_phone ?? ''),
            'customer_email' => (string) ($r->customer_email ?? ''),
            'seller_name'    => (string) ($r->seller_name ?? ''),
            'interest'       => (string) ($r->interest ?? ''),
        ];
        $after = [
            'customer_name'  => $name,
            'customer_phone' => $phone,
            'customer_email' => $email,
            'seller_name'    => $seller ? (string) $seller->name : '',
            'interest'       => $interest,
        ];

        $reservationModel->update($id, [
            'customer_name'  => $name,
            'customer_phone' => $phone !== '' ? $phone : null,
            'customer_email' => $email !== '' ? $email : null,
            'seller_id'      => $seller ? (int) $seller->id : null,
            'seller_name'    => $seller ? $seller->name : null,
            'interest'       => $interest !== '' ? $interest : null,
        ]);

        // Auditoria: registra cada campo alterado (valor antigo → novo)
        (new ReservationAudit())->recordChanges(
            $id,
            $before,
            $after,
            ['customer_name', 'customer_phone', 'customer_email', 'seller_name', 'interest'],
            'Totem'
        );

        $log = new ActivityLog();
        $log->log('totem.reservation_updated', 'room_reservation', $id, "Reserva editada no totem");

        // Notifica a atualização com os dados já novos
        $updated = $reservationModel->find($id);
        try {
            $notifier = new NotificationService();
            $notifier->notifyReservation($this->buildReservationNotificationData($updated, 'updated'), 'updated');
        } catch (\Throwable $e) {
            error_log('[TotemController] Falha ao notificar atualização: ' . $e->getMessage());
        }

        $this->json(['success' => true, 'message' => 'Reserva atualizada!']);
    }

    /**
     * Monta os dados de notificação a partir de um objeto de reserva.
     */
    private function buildReservationNotificationData(object $r, string $event): array
    {
        $roomModel = new Room();
        $room = $roomModel->find((int) $r->room_id);

        $itemModel = new RoomItem();
        $items = array_map(fn($it) => [
            'name'        => $it->name,
            'description' => $it->description,
        ], $itemModel->getActiveByRoom((int) $r->room_id));

        $sellerEmail = null;
        $sellerPhone = null;
        if (!empty($r->seller_id)) {
            $seller = (new Seller())->find((int) $r->seller_id);
            if ($seller) {
                $sellerEmail = $seller->email ?? null;
                $sellerPhone = $seller->phone ?? null;
            }
        }

        return [
            'reservation_id' => (int) $r->id,
            'room'           => $room ? $room->name : '',
            'date'           => date('d/m/Y', strtotime($r->reservation_date)),
            'start'          => substr($r->start_time, 0, 5),
            'end'            => substr($r->end_time, 0, 5),
            'customer_name'  => $r->customer_name,
            'customer_phone' => $r->customer_phone,
            'customer_email' => $r->customer_email,
            'seller_id'      => $r->seller_id ? (int) $r->seller_id : null,
            'seller_name'    => $r->seller_name,
            'seller_email'   => $sellerEmail,
            'seller_phone'   => $sellerPhone,
            'interest'       => $r->interest ?? null,
            'status'         => $event === 'cancelled' ? 'cancelled' : ($r->status ?? 'reserved'),
            'items'          => $items,
        ];
    }

    /**
     * Monta as configurações do totem já convertidas.
     */
    private function totemConfig(?object $unit = null): array
    {
        $slot = max(5, (int) $this->settingModel->getValue('totem_slot_minutes', 30));
        $min  = max($slot, (int) $this->settingModel->getValue('totem_min_duration', 30));
        $max  = max($min, (int) $this->settingModel->getValue('totem_max_duration', 120));
        $default = (int) $this->settingModel->getValue('totem_default_duration', $slot);

        // A duração padrão deve estar dentro dos limites e alinhada ao intervalo.
        $default = max($min, min($max, $default));
        $default = (int) (round($default / $slot) * $slot);
        if ($default < $min) {
            $default = $min;
        }

        // Horários: se houver unidade, usa os horários dela; senão, os globais.
        $openTime  = $unit ? substr($unit->open_time, 0, 5) : (string) $this->settingModel->getValue('totem_open_time', '08:00');
        $closeTime = $unit ? substr($unit->close_time, 0, 5) : (string) $this->settingModel->getValue('totem_close_time', '18:00');

        return [
            'open_time'        => $openTime,
            'close_time'       => $closeTime,
            'slot_minutes'     => $slot,
            'min_duration'     => $min,
            'max_duration'     => $max,
            'default_duration' => $default,
            'buffer_minutes'   => max(0, (int) $this->settingModel->getValue('totem_buffer_minutes', 0)),
            'advance_minutes'  => (int) $this->settingModel->getValue('totem_advance_minutes', 0),
            'require_email'    => (bool) $this->settingModel->getValue('totem_require_email', false),
            'refresh_seconds'  => (int) $this->settingModel->getValue('totem_refresh_seconds', 15),
        ];
    }

    /**
     * Gera as janelas de horário do dia marcando as ocupadas.
     */
    private function buildSlots(array $reservations, bool $isToday = true, ?array $config = null): array
    {
        $config = $config ?? $this->totemConfig();
        $slotSeconds = $config['slot_minutes'] * 60;
        $open  = strtotime($config['open_time']);
        $close = strtotime($config['close_time']);
        // Em datas futuras nenhum horário é "passado"
        $nowSeconds = $isToday ? strtotime(date('H:i:s')) : 0;

        // Monta a lista base de janelas com estado (ocupada/passada)
        $slots = [];
        for ($t = $open; $t + $slotSeconds <= $close; $t += $slotSeconds) {
            $slotStart = date('H:i:s', $t);
            $slotEnd   = date('H:i:s', $t + $slotSeconds);

            $occupied = false;
            $reservationId = null;
            $bufferSeconds = $config['buffer_minutes'] * 60;
            foreach ($reservations as $r) {
                // Bloqueia o horário da reserva + o buffer antes e depois dela
                $rStart = strtotime($r->start_time) - $bufferSeconds;
                $rEnd   = strtotime($r->end_time) + $bufferSeconds;
                if ($t < $rEnd && ($t + $slotSeconds) > $rStart) {
                    $occupied = true;
                    // Só marca como reserva "clicável" a janela exata da reserva (não o buffer)
                    if ($r->start_time < $slotEnd && $r->end_time > $slotStart) {
                        $reservationId = (int) $r->id;
                    }
                    break;
                }
            }

            $past = ($t + $slotSeconds) <= $nowSeconds;

            $slots[] = [
                'time'           => $t,
                'start'          => substr($slotStart, 0, 5),
                'end'            => substr($slotEnd, 0, 5),
                'occupied'       => $occupied,
                'past'           => $past,
                'available'      => !$occupied && !$past,
                'reservation_id' => $reservationId,
            ];
        }

        // Calcula quanto tempo contíguo livre existe a partir de cada janela,
        // limitado pela duração máxima permitida.
        $count = count($slots);
        for ($i = 0; $i < $count; $i++) {
            if (!$slots[$i]['available']) {
                $slots[$i]['max_minutes'] = 0;
                continue;
            }
            $free = 0;
            for ($j = $i; $j < $count; $j++) {
                if (!$slots[$j]['available']) {
                    break;
                }
                $free += $config['slot_minutes'];
                if ($free >= $config['max_duration']) {
                    break;
                }
            }
            $slots[$i]['max_minutes'] = min($free, $config['max_duration']);
        }

        // Remove a chave interna 'time' antes de enviar
        return array_map(function ($s) {
            unset($s['time']);
            return $s;
        }, $slots);
    }
}
