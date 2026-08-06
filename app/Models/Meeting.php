<?php

namespace App\Models;

use App\Core\Model;

class Meeting extends Model
{
    protected string $table = 'meetings';

    /**
     * Lista reuniões com dados do organizador.
     */
    public function allWithOrganizer(string $orderBy = 'meeting_date DESC, start_time ASC'): array
    {
        $sql = "SELECT m.*, u.name as organizer_name, u.email as organizer_email
                FROM {$this->table} m
                INNER JOIN users u ON m.organizer_id = u.id
                ORDER BY {$orderBy}";
        return $this->db()->query($sql);
    }

    /**
     * Busca reuniões de um usuário (como organizador ou participante).
     */
    public function getByUser(int $userId): array
    {
        $sql = "SELECT DISTINCT m.*, u.name as organizer_name
                FROM {$this->table} m
                INNER JOIN users u ON m.organizer_id = u.id
                LEFT JOIN meeting_participants mp ON mp.meeting_id = m.id
                WHERE m.organizer_id = :user_id OR mp.user_id = :user_id2
                ORDER BY m.meeting_date DESC, m.start_time ASC";
        return $this->db()->query($sql, ['user_id' => $userId, 'user_id2' => $userId]);
    }

    /**
     * Busca reuniões de uma data específica.
     */
    public function getByDate(string $date): array
    {
        $sql = "SELECT m.*, u.name as organizer_name
                FROM {$this->table} m
                INNER JOIN users u ON m.organizer_id = u.id
                WHERE m.meeting_date = :date AND m.status != 'cancelled'
                ORDER BY m.start_time ASC";
        return $this->db()->query($sql, ['date' => $date]);
    }

    /**
     * Busca reuniões em um período (para o calendário).
     */
    public function getByPeriod(string $startDate, string $endDate, ?int $userId = null): array
    {
        $sql = "SELECT DISTINCT m.*, u.name as organizer_name
                FROM {$this->table} m
                INNER JOIN users u ON m.organizer_id = u.id
                LEFT JOIN meeting_participants mp ON mp.meeting_id = m.id
                WHERE m.meeting_date BETWEEN :start AND :end
                AND m.status != 'cancelled'";
        $params = ['start' => $startDate, 'end' => $endDate];

        if ($userId) {
            $sql .= " AND (m.organizer_id = :user_id OR mp.user_id = :user_id2)";
            $params['user_id'] = $userId;
            $params['user_id2'] = $userId;
        }

        $sql .= " ORDER BY m.meeting_date, m.start_time";
        return $this->db()->query($sql, $params);
    }

    /**
     * Verifica conflito de horário para um usuário em uma data.
     */
    public function hasConflict(string $date, string $startTime, string $endTime, int $userId, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} m
                LEFT JOIN meeting_participants mp ON mp.meeting_id = m.id
                WHERE m.meeting_date = :date
                AND m.status NOT IN ('cancelled')
                AND (m.organizer_id = :user_id OR mp.user_id = :user_id2)
                AND (
                    (m.start_time < :end_time AND m.end_time > :start_time)
                )";
        $params = [
            'date'       => $date,
            'user_id'    => $userId,
            'user_id2'   => $userId,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ];

        if ($excludeId) {
            $sql .= " AND m.id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = $this->db()->query($sql, $params);
        return (int) $result[0]->total > 0;
    }

    /**
     * Adiciona participantes a uma reunião.
     */
    public function addParticipants(int $meetingId, array $userIds): void
    {
        foreach ($userIds as $userId) {
            $this->db()->execute(
                "INSERT IGNORE INTO meeting_participants (meeting_id, user_id) VALUES (:meeting_id, :user_id)",
                ['meeting_id' => $meetingId, 'user_id' => $userId]
            );
        }
    }

    /**
     * Remove todos os participantes de uma reunião.
     */
    public function clearParticipants(int $meetingId): void
    {
        $this->db()->execute(
            "DELETE FROM meeting_participants WHERE meeting_id = :meeting_id",
            ['meeting_id' => $meetingId]
        );
    }

    /**
     * Retorna participantes de uma reunião.
     */
    public function getParticipants(int $meetingId): array
    {
        $sql = "SELECT u.id, u.name, u.email, mp.status as participation_status
                FROM meeting_participants mp
                INNER JOIN users u ON mp.user_id = u.id
                WHERE mp.meeting_id = :meeting_id
                ORDER BY u.name";
        return $this->db()->query($sql, ['meeting_id' => $meetingId]);
    }

    /**
     * Conta reuniões por status.
     */
    public function countByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as total FROM {$this->table} GROUP BY status";
        return $this->db()->query($sql);
    }

    /**
     * Próximas reuniões a partir de agora.
     */
    public function getUpcoming(int $limit = 5, ?int $userId = null): array
    {
        $sql = "SELECT DISTINCT m.*, u.name as organizer_name
                FROM {$this->table} m
                INNER JOIN users u ON m.organizer_id = u.id
                LEFT JOIN meeting_participants mp ON mp.meeting_id = m.id
                WHERE (m.meeting_date > CURDATE() OR (m.meeting_date = CURDATE() AND m.start_time >= CURTIME()))
                AND m.status IN ('scheduled', 'confirmed')";
        $params = [];

        if ($userId) {
            $sql .= " AND (m.organizer_id = :user_id OR mp.user_id = :user_id2)";
            $params['user_id'] = $userId;
            $params['user_id2'] = $userId;
        }

        $sql .= " ORDER BY m.meeting_date ASC, m.start_time ASC LIMIT {$limit}";
        return $this->db()->query($sql, $params);
    }
}
