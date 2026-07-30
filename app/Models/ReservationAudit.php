<?php

namespace App\Models;

use App\Core\Model;

class ReservationAudit extends Model
{
    protected string $table = 'reservation_audit';

    /**
     * Rótulos amigáveis dos campos auditados.
     */
    public const FIELD_LABELS = [
        'reservation_date' => 'Data',
        'start_time'       => 'Hora início',
        'end_time'         => 'Hora fim',
        'customer_name'    => 'Cliente',
        'customer_phone'   => 'Telefone',
        'customer_email'   => 'E-mail',
        'seller_name'      => 'Vendedor',
        'room'             => 'Sala',
        'interest'         => 'Interesse',
        'status'           => 'Status',
    ];

    /**
     * Registra uma linha de auditoria (uma por campo alterado).
     */
    public function record(?int $reservationId, string $action, ?string $field, ?string $old, ?string $new, ?string $actor = null): int
    {
        return $this->create([
            'reservation_id' => $reservationId,
            'action'         => $action,
            'field'          => $field,
            'old_value'      => $old !== null ? mb_substr($old, 0, 255) : null,
            'new_value'      => $new !== null ? mb_substr($new, 0, 255) : null,
            'actor'          => $actor,
            'ip_address'     => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    /**
     * Compara dois conjuntos de dados e registra cada diferença.
     * $fields: lista de chaves a auditar.
     */
    public function recordChanges(?int $reservationId, array $before, array $after, array $fields, ?string $actor = null): int
    {
        $count = 0;
        foreach ($fields as $field) {
            $old = isset($before[$field]) ? (string) $before[$field] : '';
            $new = isset($after[$field]) ? (string) $after[$field] : '';
            if ($old !== $new) {
                $this->record($reservationId, 'updated', $field, $old, $new, $actor);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Últimos registros de auditoria com nome do cliente da reserva.
     */
    public function getRecent(int $limit = 300): array
    {
        $limit = max(1, min(1000, $limit));
        $sql = "SELECT a.*, r.customer_name
                FROM {$this->table} a
                LEFT JOIN room_reservations r ON a.reservation_id = r.id
                ORDER BY a.created_at DESC, a.id DESC
                LIMIT {$limit}";
        return $this->db()->query($sql);
    }
}
