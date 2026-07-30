<?php

namespace App\Models;

use App\Core\Model;

class RoomReservation extends Model
{
    protected string $table = 'room_reservations';

    /**
     * Busca reserva pelo id da janela (sala + data + horário de início).
     */
    public function findSlot(int $roomId, string $date, string $startTime): ?object
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE room_id = :room_id
                AND reservation_date = :date
                AND start_time = :start_time
                AND status != 'cancelled'
                ORDER BY id DESC LIMIT 1";
        $result = $this->db()->query($sql, [
            'room_id'    => $roomId,
            'date'       => $date,
            'start_time' => $startTime,
        ]);
        return $result[0] ?? null;
    }

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
     * Lista reservas recentes com o nome da sala (para administração).
     */
    public function getRecentWithRoom(int $limit = 200, ?int $unitId = null): array
    {
        $limit = max(1, min(500, $limit));
        $unitFilter = $unitId !== null ? " WHERE rm.unit_id = :unit_id" : "";
        $params = $unitId !== null ? ['unit_id' => $unitId] : [];
        $sql = "SELECT r.*, rm.name AS room_name, u.name AS unit_name
                FROM {$this->table} r
                INNER JOIN rooms rm ON r.room_id = rm.id
                LEFT JOIN units u ON rm.unit_id = u.id
                {$unitFilter}
                ORDER BY r.reservation_date DESC, r.start_time DESC
                LIMIT {$limit}";
        return $this->db()->query($sql, $params);
    }

    /**
     * Uma reserva com o nome da sala.
     */
    public function findWithRoom(int $id): ?object
    {
        $sql = "SELECT r.*, rm.name AS room_name
                FROM {$this->table} r
                INNER JOIN rooms rm ON r.room_id = rm.id
                WHERE r.id = :id LIMIT 1";
        $result = $this->db()->query($sql, ['id' => $id]);
        return $result[0] ?? null;
    }

    /**
     * Reservas ativas em um período, com o nome da sala (para o calendário).
     */
    public function getByPeriodWithRoom(string $startDate, string $endDate): array
    {
        $sql = "SELECT r.*, rm.name AS room_name
                FROM {$this->table} r
                INNER JOIN rooms rm ON r.room_id = rm.id
                WHERE r.reservation_date BETWEEN :start AND :end
                AND r.status != 'cancelled'
                ORDER BY r.reservation_date ASC, r.start_time ASC";
        return $this->db()->query($sql, ['start' => $startDate, 'end' => $endDate]);
    }

    /**
     * Histórico de clientes: nome, contato, vendedor e sala reservada.
     * Filtra opcionalmente por busca no nome/telefone/e-mail.
     */
    public function getClientHistory(string $search = '', int $limit = 300, ?int $unitId = null): array
    {
        $limit = max(1, min(1000, $limit));
        $params = [];
        $where = "1=1";

        if ($search !== '') {
            $where .= " AND (r.customer_name LIKE :s1 OR r.customer_phone LIKE :s2 OR r.customer_email LIKE :s3)";
            $like = '%' . $search . '%';
            $params['s1'] = $like;
            $params['s2'] = $like;
            $params['s3'] = $like;
        }
        if ($unitId !== null) {
            $where .= " AND rm.unit_id = :unit_id";
            $params['unit_id'] = $unitId;
        }

        $sql = "SELECT r.id, r.customer_name, r.customer_phone, r.customer_email,
                       r.seller_name, r.interest, r.reservation_date, r.start_time, r.end_time,
                       r.status, rm.name AS room_name, u.name AS unit_name
                FROM {$this->table} r
                INNER JOIN rooms rm ON r.room_id = rm.id
                LEFT JOIN units u ON rm.unit_id = u.id
                WHERE {$where}
                ORDER BY r.reservation_date DESC, r.start_time DESC
                LIMIT {$limit}";
        return $this->db()->query($sql, $params);
    }

    /**
     * Ranking de salas por número de reservas (procura), últimos N dias.
     */
    public function getRoomRanking(int $days = 30, int $limit = 5, ?int $unitId = null): array
    {
        $days = max(1, $days);
        $unitFilter = $unitId !== null ? " AND rm.unit_id = :unit_id" : "";
        $params = $unitId !== null ? ['unit_id' => $unitId] : [];
        $sql = "SELECT rm.id, rm.name,
                       COUNT(r.id) AS total,
                       COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(r.end_time, r.start_time))), 0) AS seconds
                FROM rooms rm
                LEFT JOIN {$this->table} r
                    ON r.room_id = rm.id
                    AND r.status != 'cancelled'
                    AND r.reservation_date >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
                WHERE rm.active = 1{$unitFilter}
                GROUP BY rm.id, rm.name
                ORDER BY total DESC, seconds DESC
                LIMIT {$limit}";
        return $this->db()->query($sql, $params);
    }

    /**
     * Totais gerais das reservas nos últimos N dias.
     */
    public function getSummary(int $days = 30, ?int $unitId = null): object
    {
        $days = max(1, $days);
        $join = $unitId !== null ? " INNER JOIN rooms rm ON r.room_id = rm.id" : "";
        $unitFilter = $unitId !== null ? " AND rm.unit_id = :unit_id" : "";
        $params = $unitId !== null ? ['unit_id' => $unitId] : [];
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN r.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
                    SUM(CASE WHEN r.reservation_date = CURDATE() AND r.status != 'cancelled' THEN 1 ELSE 0 END) AS today,
                    COALESCE(SUM(CASE WHEN r.status != 'cancelled' THEN TIME_TO_SEC(TIMEDIFF(r.end_time, r.start_time)) ELSE 0 END), 0) AS seconds
                FROM {$this->table} r{$join}
                WHERE r.reservation_date >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY){$unitFilter}";
        $res = $this->db()->query($sql, $params);
        return $res[0] ?? (object) ['total' => 0, 'cancelled' => 0, 'today' => 0, 'seconds' => 0];
    }

    /**
     * Vendedores com mais reservas (últimos N dias).
     */
    public function getSellerRanking(int $days = 30, int $limit = 5, ?int $unitId = null): array
    {
        $days = max(1, $days);
        $join = $unitId !== null ? " INNER JOIN rooms rm ON r.room_id = rm.id" : "";
        $unitFilter = $unitId !== null ? " AND rm.unit_id = :unit_id" : "";
        $params = $unitId !== null ? ['unit_id' => $unitId] : [];
        $sql = "SELECT r.seller_name, COUNT(*) AS total
                FROM {$this->table} r{$join}
                WHERE r.status != 'cancelled'
                    AND r.seller_name IS NOT NULL AND r.seller_name != ''
                    AND r.reservation_date >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY){$unitFilter}
                GROUP BY r.seller_name
                ORDER BY total DESC
                LIMIT {$limit}";
        return $this->db()->query($sql, $params);
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
                AND start_time <= :time1 AND end_time > :time2
                ORDER BY start_time ASC LIMIT 1";
        $result = $this->db()->query($sql, [
            'room_id' => $roomId,
            'date'    => $date,
            'time1'   => $time,
            'time2'   => $time,
        ]);
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
