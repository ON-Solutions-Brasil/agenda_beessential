<?php

namespace App\Models;

use App\Core\Model;

class NotificationLog extends Model
{
    protected string $table = 'notification_logs';

    /**
     * Registra um evento de envio.
     */
    public function record(string $channel, string $status, ?string $recipient = null, ?string $subject = null, ?string $error = null, ?int $reservationId = null, ?string $payload = null): int
    {
        return $this->create([
            'reservation_id' => $reservationId,
            'channel'        => $channel,
            'recipient'      => $recipient,
            'subject'        => $subject,
            'status'         => $status,
            'error'          => $error,
            'payload'        => $payload,
        ]);
    }

    /**
     * Últimos registros para a tela de logs de envio.
     */
    public function getRecent(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT {$limit}";
        return $this->db()->query($sql);
    }
}
