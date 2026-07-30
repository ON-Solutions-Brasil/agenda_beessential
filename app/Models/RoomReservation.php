<?php

namespace App\Models;

use App\Core\Model;

class RoomReservation extends Model
{
    protected string $table = 'room_reservations';

    /**
     * Reservas ativas de uma sala em uma data (não canceladas).
     */
    public function getByRoomAndDate(int $roomId, string $date): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE room_id = :room_id
                AND reservation_date = :date
                AND status != 'cancelled'
                ORDER BY start_time ASC";
        return $this->db()->query($sql, ['room_id' => $roomId, 'date' => $date]);
    }

    /**
     * Todas as reservas ativas de uma data (todas as salas).
     */
    public function getByDate(string $date): array
    {
        $sql = "SELECT r.*, rm.name AS room_name
                FROM {$this->table} r
                INNER JOIN rooms rm ON r.room_id = rm.id
                WHERE r.reservation_date = :date
                AND r.status != 'cancelled'
                ORDER BY r.start_time ASC";
        return $this->db()->query($sql, ['date' => $date]);
    }

    /**
     * Verifica se há sobreposição de horário para a sala.
     */
    public function hasConflict(int $roomId, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table}
                WHERE room_id = :room_id
                AND reservation_date = :date
                AND status NOT IN ('cancelled', 'completed')
                AND (start_time < :end_time AND end_time > :start_time)";
        $params = [
            'room_id'    => $roomId,
            'date'       => $date,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = $this->db()->query($sql, $params);
        return (int) $result[0]->total > 0;
    }

    /**
     * Retorna a reserva atualmente em andamento para uma sala numa data/hora.
     */
    public function getCurrent(int $roomId, string $date, string $time): ?object
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE room_id = :room_id
                AND reservation_date = :date
                AND status != 'cancelled'
                AND start_time <= :time AND end_time > :time
                ORDER BY start_time ASC LIMIT 1";
        $result = $this->db()->query($sql, ['room_id' => $roomId, 'date' => $date, 'time' => $time]);
        return $result[0] ?? null;
    }

    /**
     * Próxima reserva futura de uma sala numa data.
     */
    public function getNext(int $roomId, string $date, string $time): ?object
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE room_id = :room_id
                AND reservation_date = :date
                AND status != 'cancelled'
                AND start_time > :time
                ORDER BY start_time ASC LIMIT 1";
        $result = $this->db()->query($sql, ['room_id' => $roomId, 'date' => $date, 'time' => $time]);
        return $result[0] ?? null;
    }
}
