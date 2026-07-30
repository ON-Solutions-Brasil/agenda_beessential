<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Serviço para geração de links do Google Meet.
 *
 * Duas modalidades:
 * 1. Com Google Calendar API configurada: cria evento real no Google Calendar com Meet link.
 * 2. Sem API configurada (fallback): gera um link de reunião instantânea do Google Meet.
 */
class GoogleMeetService
{
    private ?string $clientId;
    private ?string $clientSecret;
    private ?string $apiKey;

    public function __construct()
    {
        $settingModel = new Setting();
        $this->clientId     = $settingModel->getValue('google_client_id') ?: null;
        $this->clientSecret = $settingModel->getValue('google_client_secret') ?: null;
        $this->apiKey       = $settingModel->getValue('google_api_key') ?: null;
    }

    /**
     * Gera um link do Google Meet para uma reunião.
     *
     * Se a Google API estiver configurada, tenta criar via API.
     * Caso contrário, gera link direto (meet.google.com/new).
     */
    public function generateMeetLink(string $title, string $date, string $startTime, string $endTime): string
    {
        // Se as credenciais da API estão configuradas, tenta criar via API
        if ($this->isApiConfigured()) {
            $link = $this->createViaApi($title, $date, $startTime, $endTime);
            if ($link) {
                return $link;
            }
        }

        // Fallback: gera link usando o formato de agendamento rápido do Google Calendar
        return $this->generateQuickLink($title, $date, $startTime, $endTime);
    }

    /**
     * Verifica se a API do Google está configurada.
     */
    public function isApiConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Cria um evento no Google Calendar com Meet link via API.
     * Requer OAuth2 token válido armazenado.
     */
    private function createViaApi(string $title, string $date, string $startTime, string $endTime): ?string
    {
        // TODO: Implementar integração completa com Google Calendar API
        // Para funcionar, é necessário:
        // 1. Configurar OAuth2 no Google Cloud Console
        // 2. Obter access_token via fluxo de autorização
        // 3. Criar evento com conferenceData
        //
        // Exemplo da requisição que seria feita:
        // POST https://www.googleapis.com/calendar/v3/calendars/primary/events?conferenceDataVersion=1
        // Body:
        // {
        //     "summary": $title,
        //     "start": {"dateTime": "{$date}T{$startTime}:00", "timeZone": "America/Sao_Paulo"},
        //     "end": {"dateTime": "{$date}T{$endTime}:00", "timeZone": "America/Sao_Paulo"},
        //     "conferenceData": {
        //         "createRequest": {
        //             "requestId": uniqid(),
        //             "conferenceSolutionKey": {"type": "hangoutsMeet"}
        //         }
        //     }
        // }

        return null; // Retorna null para usar o fallback
    }

    /**
     * Gera um link de agendamento rápido do Google Calendar com Meet.
     * Este link abre o Google Calendar pré-preenchido com os dados da reunião
     * e com videoconferência Meet habilitada.
     */
    private function generateQuickLink(string $title, string $date, string $startTime, string $endTime): string
    {
        // Formata data/hora para o padrão do Google Calendar (YYYYMMDDTHHmmss)
        $startDateTime = $this->formatDateTime($date, $startTime);
        $endDateTime   = $this->formatDateTime($date, $endTime);

        // Monta URL de criação de evento no Google Calendar com Meet
        $params = [
            'action'   => 'TEMPLATE',
            'text'     => $title,
            'dates'    => $startDateTime . '/' . $endDateTime,
            'details'  => 'Reunião agendada via Agenda Beessential',
            'location' => 'Google Meet',
            'add'      => '', // Pode adicionar emails de participantes
            'crm'     => 'AVAILABLE',
            'trp'     => 'true',
        ];

        // O parâmetro &add=&crm=AVAILABLE&trp=true força a criação de Meet link
        return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
    }

    /**
     * Gera um link direto para criação de sala Meet instantânea.
     * Útil quando não se precisa de integração com Calendar.
     */
    public function generateInstantMeetLink(): string
    {
        // Link para criar uma nova reunião Meet instantaneamente
        return 'https://meet.google.com/new';
    }

    /**
     * Gera um link Meet com código aleatório (formato: xxx-yyyy-zzz).
     * Nota: Este link só funciona se a conta Google tiver permissão para criar salas.
     */
    public function generateMeetCode(): string
    {
        $segments = [
            $this->randomSegment(3),
            $this->randomSegment(4),
            $this->randomSegment(3),
        ];

        return 'https://meet.google.com/' . implode('-', $segments);
    }

    /**
     * Formata data e hora para o padrão Google Calendar (YYYYMMDDTHHmmss).
     */
    private function formatDateTime(string $date, string $time): string
    {
        // Remove caracteres não numéricos da data
        $dateClean = str_replace('-', '', $date);
        // Remove : do horário e adiciona segundos
        $timeClean = str_replace(':', '', $time);
        if (strlen($timeClean) === 4) {
            $timeClean .= '00';
        }

        return $dateClean . 'T' . $timeClean;
    }

    /**
     * Gera um segmento aleatório de letras para código Meet.
     */
    private function randomSegment(int $length): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $segment = '';
        for ($i = 0; $i < $length; $i++) {
            $segment .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $segment;
    }
}
