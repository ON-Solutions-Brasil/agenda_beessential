<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\View;
use App\Models\Setting;
use App\Models\Room;
use App\Models\RoomReservation;
use App\Models\RoomItem;
use App\Models\ActivityLog;

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
     * Verifica se o acesso ao totem já foi liberado via PIN.
     */
    private function requireTotemSession(): void
    {
        $this->requireTotemEnabled();
        if (!Session::has('totem_access')) {
            $this->redirect('/totem/pin');
            exit;
        }
    }

    /**
     * Tela de digitação do PIN.
     */
    public function pin(): void
    {
        $this->requireTotemEnabled();

        if (Session::has('totem_access')) {
            $this->redirect('/totem');
            return;
        }

        View::render('totem/pin', [], 'totem');
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
        $expected = (string) $this->settingModel->getValue('totem_pin', '');

        if ($pin !== '' && hash_equals($expected, $pin)) {
            Session::set('totem_access', true);
            $log = new ActivityLog();
            $log->log('totem.access', 'totem', null, 'Acesso ao modo Totem via PIN');
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
        $this->redirect('/totem/pin');
    }

    /**
     * Tela principal do totem (dashboard de salas).
     */
    public function index(): void
    {
        $this->requireTotemSession();

        View::render('totem/index', [
            'config' => $this->totemConfig(),
        ], 'totem');
    }

    /**
     * Retorna o estado atual das salas em JSON (para atualização em tempo real).
     */
    public function rooms(): void
    {
        $this->requireTotemSession();

        $date = date('Y-m-d');
        $now  = date('H:i:s');

        $roomModel = new Room();
        $reservationModel = new RoomReservation();
        $itemModel = new RoomItem();
        $rooms = $roomModel->getTotemRooms();

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
                'slots'       => $this->buildSlots($reservations),
            ];
        }

        $this->json([
            'date'       => $date,
            'now'        => substr($now, 0, 5),
            'rooms'      => $data,
            'refresh_ms' => ((int) $this->settingModel->getValue('totem_refresh_seconds', 15)) * 1000,
        ]);
    }

    /**
     * Registra uma nova reserva feita pelo totem.
     */
    public function reserve(): void
    {
        $this->requireTotemSession();

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

        $config = $this->totemConfig();

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

        // Confirma que a sala existe e está disponível no totem
        $roomModel = new Room();
        $room = $roomModel->find($roomId);
        if (!$room || (int) $room->active !== 1 || (int) $room->show_in_totem !== 1) {
            $this->json(['success' => false, 'message' => 'Sala indisponível.'], 404);
            return;
        }

        $date = date('Y-m-d');
        $endTime = date('H:i:s', strtotime($startTime) + $duration * 60);
        $startTime = date('H:i:s', strtotime($startTime));

        // Dentro do horário de funcionamento
        if ($startTime < $config['open_time'] . ':00' || $endTime > $config['close_time'] . ':00') {
            $this->json(['success' => false, 'message' => 'Horário fora do funcionamento.'], 422);
            return;
        }

        // Antecedência mínima e não permitir horário passado
        $slotStart = strtotime($date . ' ' . $startTime);
        $minStart  = time() + $config['advance_minutes'] * 60;
        if ($slotStart < $minStart) {
            $this->json(['success' => false, 'message' => 'Horário indisponível para reserva.'], 422);
            return;
        }

        $reservationModel = new RoomReservation();
        if ($reservationModel->hasConflict($roomId, $date, $startTime, $endTime)) {
            $this->json(['success' => false, 'message' => 'Este horário acabou de ser reservado.'], 409);
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
            'status'           => 'reserved',
            'source'           => 'totem',
        ]);

        $log = new ActivityLog();
        $log->log('totem.reserve', 'room_reservation', $id, "Reserva no totem: {$room->name} {$name}");

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
     * Monta as configurações do totem já convertidas.
     */
    private function totemConfig(): array
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

        return [
            'open_time'        => (string) $this->settingModel->getValue('totem_open_time', '08:00'),
            'close_time'       => (string) $this->settingModel->getValue('totem_close_time', '18:00'),
            'slot_minutes'     => $slot,
            'min_duration'     => $min,
            'max_duration'     => $max,
            'default_duration' => $default,
            'advance_minutes'  => (int) $this->settingModel->getValue('totem_advance_minutes', 0),
            'require_email'    => (bool) $this->settingModel->getValue('totem_require_email', false),
            'refresh_seconds'  => (int) $this->settingModel->getValue('totem_refresh_seconds', 15),
        ];
    }

    /**
     * Gera as janelas de horário do dia marcando as ocupadas.
     */
    private function buildSlots(array $reservations): array
    {
        $config = $this->totemConfig();
        $slotSeconds = $config['slot_minutes'] * 60;
        $open  = strtotime($config['open_time']);
        $close = strtotime($config['close_time']);
        $nowSeconds = strtotime(date('H:i:s'));

        // Monta a lista base de janelas com estado (ocupada/passada)
        $slots = [];
        for ($t = $open; $t + $slotSeconds <= $close; $t += $slotSeconds) {
            $slotStart = date('H:i:s', $t);
            $slotEnd   = date('H:i:s', $t + $slotSeconds);

            $occupied = false;
            foreach ($reservations as $r) {
                if ($r->start_time < $slotEnd && $r->end_time > $slotStart) {
                    $occupied = true;
                    break;
                }
            }

            $past = ($t + $slotSeconds) <= $nowSeconds;

            $slots[] = [
                'time'      => $t,
                'start'     => substr($slotStart, 0, 5),
                'end'       => substr($slotEnd, 0, 5),
                'occupied'  => $occupied,
                'past'      => $past,
                'available' => !$occupied && !$past,
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
