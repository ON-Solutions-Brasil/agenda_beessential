<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\NotificationLog;

/**
 * Serviço de notificações da reserva: e-mail (SMTP) e webhook.
 * SMTP implementado via socket puro para não depender de bibliotecas externas.
 * Todos os envios são registrados em notification_logs.
 */
class NotificationService
{
    private Setting $settings;
    private NotificationLog $logs;

    public function __construct()
    {
        $this->settings = new Setting();
        $this->logs = new NotificationLog();
    }

    /**
     * Notifica visitante e vendedor sobre a reserva.
     * Nunca lança exceção: falhas são registradas e não bloqueiam a reserva.
     *
     * @return array Resumo do que foi enviado.
     */
    public function notifyReservation(array $data): array
    {
        $reservationId = $data['reservation_id'] ?? null;
        $result = ['email_customer' => false, 'email_seller' => false, 'webhook' => false];

        // E-mail para o visitante
        if (!empty($data['customer_email'])) {
            $subject = 'Confirmação da sua visita - ' . ($data['room'] ?? 'Sala');
            $result['email_customer'] = $this->sendEmail(
                $data['customer_email'],
                $data['customer_name'] ?? '',
                $subject,
                $this->buildCustomerBody($data),
                $reservationId
            );
        }

        // E-mail para o vendedor
        if (!empty($data['seller_email'])) {
            $subject = 'Novo agendamento - ' . ($data['room'] ?? 'Sala') . ' (' . ($data['customer_name'] ?? '') . ')';
            $result['email_seller'] = $this->sendEmail(
                $data['seller_email'],
                $data['seller_name'] ?? '',
                $subject,
                $this->buildSellerBody($data),
                $reservationId
            );
        }

        // Webhook
        $result['webhook'] = $this->sendWebhook($data);

        return $result;
    }

    /**
     * Envia um e-mail via SMTP configurado e registra o resultado no log.
     */
    public function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody, ?int $reservationId = null): bool
    {
        if (!$this->settings->getValue('smtp_enabled', false)) {
            $this->logs->record('email', 'skipped', $toEmail, $subject, 'SMTP desativado', $reservationId);
            return false;
        }

        $host = (string) $this->settings->getValue('smtp_host', '');
        $port = (int) $this->settings->getValue('smtp_port', 587);
        $enc  = strtolower((string) $this->settings->getValue('smtp_encryption', 'tls'));
        $user = (string) $this->settings->getValue('smtp_username', '');
        $pass = (string) $this->settings->getValue('smtp_password', '');
        $fromEmail = (string) $this->settings->getValue('smtp_from_email', $user);
        $fromName  = (string) $this->settings->getValue('smtp_from_name', 'Agenda Beessential');

        if ($host === '' || $fromEmail === '') {
            $this->logs->record('email', 'failed', $toEmail, $subject, 'SMTP não configurado (host/remetente)', $reservationId);
            return false;
        }

        try {
            $this->smtpSend($host, $port, $enc, $user, $pass, $fromEmail, $fromName, $toEmail, $toName, $subject, $htmlBody);
            $this->logs->record('email', 'success', $toEmail, $subject, null, $reservationId);
            return true;
        } catch (\Throwable $e) {
            error_log('[NotificationService] Falha SMTP: ' . $e->getMessage());
            $this->logs->record('email', 'failed', $toEmail, $subject, $e->getMessage(), $reservationId);
            return false;
        }
    }

    /**
     * Dispara o webhook configurado com o payload da reserva e registra o log.
     */
    public function sendWebhook(array $data): bool
    {
        $reservationId = $data['reservation_id'] ?? null;

        if (!$this->settings->getValue('webhook_enabled', false)) {
            $this->logs->record('webhook', 'skipped', null, 'reservation.created', 'Webhook desativado', $reservationId);
            return false;
        }

        $url = (string) $this->settings->getValue('webhook_url', '');
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->logs->record('webhook', 'failed', $url, 'reservation.created', 'URL inválida', $reservationId);
            return false;
        }

        $payload = json_encode($this->buildWebhookPayload($data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $response = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($code >= 200 && $code < 300) {
                $this->logs->record('webhook', 'success', $url, 'reservation.created', 'HTTP ' . $code, $reservationId, $payload);
                return true;
            }

            $this->logs->record('webhook', 'failed', $url, 'reservation.created', 'HTTP ' . $code . ' ' . $curlErr, $reservationId, $payload);
            return false;
        } catch (\Throwable $e) {
            error_log('[NotificationService] Falha Webhook: ' . $e->getMessage());
            $this->logs->record('webhook', 'failed', $url, 'reservation.created', $e->getMessage(), $reservationId, $payload);
            return false;
        }
    }

    /**
     * Monta o payload completo do webhook, com todos os campos sempre presentes.
     */
    private function buildWebhookPayload(array $d): array
    {
        $str = fn($v) => isset($v) && $v !== '' ? (string) $v : null;

        $customerPhone = $str($d['customer_phone'] ?? null);
        $sellerPhone   = $str($d['seller_phone'] ?? null);

        // Itens da sala (o que o cliente vai ver)
        $items = [];
        if (!empty($d['items']) && is_array($d['items'])) {
            foreach ($d['items'] as $item) {
                if (is_array($item)) {
                    $items[] = [
                        'nome'      => $str($item['name'] ?? null),
                        'descricao' => $str($item['description'] ?? null),
                    ];
                } else {
                    $items[] = ['nome' => (string) $item, 'descricao' => null];
                }
            }
        }

        $date  = $str($d['date'] ?? null);       // dd/mm/aaaa
        $start = $str($d['start'] ?? null);      // HH:MM
        $end   = $str($d['end'] ?? null);        // HH:MM

        // Data ISO para integrações (aaaa-mm-ddTHH:MM)
        $isoStart = null;
        $isoEnd = null;
        if ($date && $start) {
            $iso = \DateTime::createFromFormat('d/m/Y', $date);
            if ($iso) {
                $isoDate = $iso->format('Y-m-d');
                $isoStart = $isoDate . 'T' . $start;
                if ($end) {
                    $isoEnd = $isoDate . 'T' . $end;
                }
            }
        }

        return [
            'event'     => 'reservation.created',
            'timestamp' => date('c'),
            'source'    => 'totem',
            'data' => [
                'reserva' => [
                    'id'     => isset($d['reservation_id']) ? (int) $d['reservation_id'] : null,
                    'status' => 'reserved',
                    'origem' => 'totem',
                ],
                'agendamento' => [
                    'data'          => $date,
                    'hora_inicio'   => $start,
                    'hora_fim'      => $end,
                    'inicio_iso'    => $isoStart,
                    'fim_iso'       => $isoEnd,
                ],
                'sala' => [
                    'nome'  => $str($d['room'] ?? null),
                    'itens' => $items,
                ],
                'visitante' => [
                    'nome'          => $str($d['customer_name'] ?? null),
                    'telefone'      => $customerPhone,
                    'telefone_e164' => $customerPhone ? $this->formatPhoneBR($customerPhone) : null,
                    'email'         => $str($d['customer_email'] ?? null),
                ],
                'vendedor' => [
                    'id'            => isset($d['seller_id']) ? (int) $d['seller_id'] : null,
                    'nome'          => $str($d['seller_name'] ?? null),
                    'telefone'      => $sellerPhone,
                    'telefone_e164' => $sellerPhone ? $this->formatPhoneBR($sellerPhone) : null,
                    'email'         => $str($d['seller_email'] ?? null),
                ],
                'interesse' => $str($d['interest'] ?? null),
            ],
        ];
    }

    /**
     * Implementação SMTP mínima via socket.
     */
    private function smtpSend(string $host, int $port, string $enc, string $user, string $pass, string $fromEmail, string $fromName, string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $transport = ($enc === 'ssl') ? "ssl://{$host}" : $host;
        $fp = @fsockopen($transport, $port, $errno, $errstr, 15);
        if (!$fp) {
            throw new \RuntimeException("Conexão falhou: {$errstr} ({$errno})");
        }
        stream_set_timeout($fp, 15);

        $read = function () use ($fp) {
            $data = '';
            while ($line = fgets($fp, 515)) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $data;
        };
        $cmd = function (string $c) use ($fp, $read) {
            fwrite($fp, $c . "\r\n");
            return $read();
        };

        $read(); // saudação
        $ehloHost = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $cmd("EHLO {$ehloHost}");

        if ($enc === 'tls') {
            $cmd('STARTTLS');
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
                fclose($fp);
                throw new \RuntimeException('Falha ao iniciar TLS');
            }
            $cmd("EHLO {$ehloHost}");
        }

        if ($user !== '') {
            $cmd('AUTH LOGIN');
            $cmd(base64_encode($user));
            $authResp = $cmd(base64_encode($pass));
            if (strpos($authResp, '235') === false) {
                fclose($fp);
                throw new \RuntimeException('Autenticação SMTP falhou');
            }
        }

        $cmd("MAIL FROM:<{$fromEmail}>");
        $cmd("RCPT TO:<{$toEmail}>");
        $dataResp = $cmd('DATA');
        if (strpos($dataResp, '354') === false) {
            fclose($fp);
            throw new \RuntimeException('Servidor recusou DATA');
        }

        $headers  = 'From: ' . $this->encodeHeader($fromName) . " <{$fromEmail}>\r\n";
        $headers .= 'To: ' . $this->encodeHeader($toName) . " <{$toEmail}>\r\n";
        $headers .= 'Subject: ' . $this->encodeHeader($subject) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";

        // Escapa pontos no início de linha (transparência SMTP)
        $body = preg_replace('/^\./m', '..', $htmlBody);
        $cmd($headers . "\r\n" . $body . "\r\n.");
        $cmd('QUIT');
        fclose($fp);

        return true;
    }

    private function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    /**
     * Formata telefone para o padrão Brasil E.164 sem o "+".
     * Ex.: "(17) 99125-3022" ou "17991253022" -> "5517991253022".
     * Retorna string vazia se não houver dígitos suficientes.
     */
    public function formatPhoneBR(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') {
            return '';
        }

        // Remove zeros à esquerda (ex.: 0DDD)
        $digits = ltrim($digits, '0');

        // Já vem com código do país (55) e tamanho compatível
        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            return $digits;
        }

        // 10 (fixo com DDD) ou 11 (celular com DDD) dígitos -> prefixa 55
        if (strlen($digits) === 10 || strlen($digits) === 11) {
            return '55' . $digits;
        }

        // Fallback: se ainda não tiver o 55, adiciona
        return str_starts_with($digits, '55') ? $digits : '55' . $digits;
    }

    private function buildCustomerBody(array $d): string
    {
        $room = htmlspecialchars($d['room'] ?? 'a sala');
        $intro = "Olá <strong>" . htmlspecialchars($d['customer_name'] ?? '') . "</strong>, sua visita está confirmada! "
            . "Preparamos <strong>" . $room . "</strong> para você conhecer de perto o que veio ver.";
        return $this->emailTemplate('Sua visita está confirmada!', $intro, $d, true);
    }

    private function buildSellerBody(array $d): string
    {
        $name = htmlspecialchars($d['customer_name'] ?? 'um visitante');
        $intro = "Novo agendamento" . (!empty($d['seller_name']) ? " para <strong>" . htmlspecialchars($d['seller_name']) . "</strong>" : '') . ". "
            . "O visitante <strong>" . $name . "</strong> tem interesse no que será apresentado abaixo. "
            . "Use este briefing para preparar o atendimento e qualificar o interesse.";
        return $this->emailTemplate('Novo agendamento recebido', $intro, $d, true);
    }

    /**
     * Template do e-mail. Quando $withBriefing, inclui o resumo do que o
     * cliente vai ver (itens da sala) e o interesse informado.
     */
    private function emailTemplate(string $title, string $intro, array $d, bool $withBriefing = false): string
    {
        $room  = htmlspecialchars($d['room'] ?? '');
        $date  = htmlspecialchars($d['date'] ?? '');
        $start = htmlspecialchars($d['start'] ?? '');
        $end   = htmlspecialchars($d['end'] ?? '');
        $name  = htmlspecialchars($d['customer_name'] ?? '');
        $phone = htmlspecialchars($d['customer_phone'] ?? '-');
        $email = htmlspecialchars($d['customer_email'] ?? '-');
        $seller = htmlspecialchars($d['seller_name'] ?? '-');

        $briefing = '';
        if ($withBriefing) {
            $briefing .= '<div style="margin-top:20px;padding:16px;background:#1c1c1c;border-left:4px solid #FFC107;border-radius:8px;">';
            $briefing .= '<div style="color:#FFC107;font-weight:bold;margin-bottom:8px;">Briefing do agendamento</div>';

            if (!empty($d['interest'])) {
                $briefing .= '<p style="margin:0 0 10px;color:#eee;"><strong style="color:#FFC107;">Interesse do visitante:</strong> '
                    . htmlspecialchars($d['interest']) . '</p>';
            }

            if (!empty($d['items']) && is_array($d['items'])) {
                $briefing .= '<div style="color:#ccc;margin-bottom:6px;">O que será apresentado nesta sala:</div><ul style="margin:0;padding-left:18px;color:#eee;">';
                foreach ($d['items'] as $item) {
                    $itemName = htmlspecialchars(is_array($item) ? ($item['name'] ?? '') : (string) $item);
                    $itemDesc = htmlspecialchars(is_array($item) ? ($item['description'] ?? '') : '');
                    $briefing .= '<li style="margin-bottom:4px;"><strong>' . $itemName . '</strong>'
                        . ($itemDesc !== '' ? ' — <span style="color:#aaa;">' . $itemDesc . '</span>' : '') . '</li>';
                }
                $briefing .= '</ul>';
            }
            $briefing .= '</div>';
        }

        return '<!DOCTYPE html><html><body style="margin:0;background:#f4f4f5;font-family:Arial,sans-serif;">'
            . '<div style="max-width:560px;margin:24px auto;background:#111;border-radius:12px;overflow:hidden;">'
            . '<div style="background:#FFC107;color:#111;padding:20px 24px;font-size:20px;font-weight:bold;">' . $title . '</div>'
            . '<div style="padding:24px;color:#eee;font-size:15px;line-height:1.6;">'
            . '<p>' . $intro . '</p>'
            . '<table style="width:100%;border-collapse:collapse;margin-top:12px;">'
            . $this->row('Sala', $room)
            . $this->row('Data', $date)
            . $this->row('Horário', $start . ' às ' . $end)
            . $this->row('Visitante', $name)
            . $this->row('Telefone', $phone)
            . $this->row('E-mail', $email)
            . $this->row('Vendedor', $seller)
            . '</table>'
            . $briefing
            . '<p style="margin-top:20px;color:#FFC107;font-weight:bold;">Agenda Beessential</p>'
            . '</div></div></body></html>';
    }

    private function row(string $label, string $value): string
    {
        return '<tr>'
            . '<td style="padding:8px 0;color:#FFC107;width:120px;">' . $label . '</td>'
            . '<td style="padding:8px 0;color:#fff;">' . $value . '</td>'
            . '</tr>';
    }
}
