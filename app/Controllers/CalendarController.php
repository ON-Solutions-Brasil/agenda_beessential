<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Meeting;
use App\Models\RoomReservation;

class CalendarController extends Controller
{
    private Meeting $meetingModel;

    public function __construct()
    {
        $this->meetingModel = new Meeting();
    }

    /**
     * Exibe a página do calendário.
     */
    public function index(): void
    {
        $this->requirePermission('calendar.view');

        $this->view('calendar/index');
    }

    /**
     * Retorna eventos em formato JSON para o calendário (FullCalendar).
     */
    public function events(): void
    {
        $this->requirePermission('calendar.view');

        $start = $this->query('start', date('Y-m-01'));
        $end   = $this->query('end', date('Y-m-t'));

        // Admin vê todas as reuniões, usuário comum vê só as dele
        $userId = null;
        if (!Auth::hasPermission('users.view')) {
            $userId = Auth::userId();
        }

        $meetings = $this->meetingModel->getByPeriod($start, $end, $userId);

        $events = [];
        foreach ($meetings as $meeting) {
            $events[] = [
                'id'              => 'm' . $meeting->id,
                'title'           => $meeting->title,
                'start'           => $meeting->meeting_date . 'T' . $meeting->start_time,
                'end'             => $meeting->meeting_date . 'T' . $meeting->end_time,
                'color'           => $meeting->color ?? '#3788d8',
                'url'             => "/meetings/{$meeting->id}",
                'extendedProps'   => [
                    'organizer'  => $meeting->organizer_name,
                    'status'     => $meeting->status,
                    'meet_link'  => $meeting->meet_link,
                    'location'   => $meeting->location,
                ],
            ];
        }

        // Reservas de salas feitas pelo totem
        $reservationModel = new RoomReservation();
        $reservations = $reservationModel->getByPeriodWithRoom($start, $end);
        foreach ($reservations as $r) {
            $title = $r->room_name . ' — ' . $r->customer_name;
            $events[] = [
                'id'            => 'r' . $r->id,
                'title'         => $title,
                'start'         => $r->reservation_date . 'T' . $r->start_time,
                'end'           => $r->reservation_date . 'T' . $r->end_time,
                'color'         => '#111111',
                'textColor'     => '#FFC107',
                'extendedProps' => [
                    'tipo'      => 'Reserva de Sala (Totem)',
                    'sala'      => $r->room_name,
                    'visitante' => $r->customer_name,
                    'telefone'  => $r->customer_phone,
                    'email'     => $r->customer_email,
                    'vendedor'  => $r->seller_name,
                    'interesse' => $r->interest,
                    'status'    => $r->status,
                ],
            ];
        }

        $this->json($events);
    }
}
