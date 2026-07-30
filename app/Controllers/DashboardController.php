<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Meeting;
use App\Models\User;
use App\Models\RoomReservation;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $meetingModel = new Meeting();
        $userModel = new User();

        // Próximas reuniões do usuário
        $upcomingMeetings = $meetingModel->getUpcoming(5, Auth::userId());

        // Reuniões de hoje
        $todayMeetings = $meetingModel->getByDate(date('Y-m-d'));

        // Estatísticas gerais (para admin)
        $stats = [];
        if (Auth::hasPermission('users.view')) {
            $stats['total_users'] = $userModel->count();
            $stats['total_meetings'] = $meetingModel->count();
            $stats['meetings_today'] = $meetingModel->count(
                "meeting_date = :date AND status != 'cancelled'",
                ['date' => date('Y-m-d')]
            );
            $stats['meetings_week'] = $meetingModel->count(
                "meeting_date BETWEEN :start AND :end AND status != 'cancelled'",
                ['start' => date('Y-m-d', strtotime('monday this week')), 'end' => date('Y-m-d', strtotime('friday this week'))]
            );
        }

        // Indicadores do Totem (reservas de salas) — visíveis para quem vê usuários (admin)
        $totemStats = null;
        $units = [];
        $selectedUnit = null;
        if (Auth::hasPermission('users.view')) {
            $reservationModel = new RoomReservation();
            $settingModel = new \App\Models\Setting();
            $unitModel = new \App\Models\Unit();
            $units = $unitModel->allOrdered();

            // Filtro por unidade (via query string)
            $unitId = (int) $this->query('unit', 0);
            $unit = $unitId > 0 ? $unitModel->find($unitId) : null;
            $selectedUnit = $unit ? (int) $unit->id : null;

            $days = 30;
            // Horário base: da unidade selecionada ou o padrão global
            if ($unit) {
                $open  = substr($unit->open_time, 0, 5);
                $close = substr($unit->close_time, 0, 5);
            } else {
                $open  = (string) $settingModel->getValue('totem_open_time', '08:00');
                $close = (string) $settingModel->getValue('totem_close_time', '18:00');
            }
            $hoursPerDay = max(1, (strtotime($close) - strtotime($open)) / 3600);
            $availableHours = $hoursPerDay * $days;

            $totemStats = [
                'summary'         => $reservationModel->getSummary($days, $selectedUnit),
                'room_ranking'    => $reservationModel->getRoomRanking($days, 6, $selectedUnit),
                'seller_ranking'  => $reservationModel->getSellerRanking($days, 5, $selectedUnit),
                'available_hours' => $availableHours,
                'hours_per_day'   => $hoursPerDay,
                'days'            => $days,
            ];
        }

        $this->view('dashboard/index', [
            'upcomingMeetings' => $upcomingMeetings,
            'todayMeetings'    => $todayMeetings,
            'stats'            => $stats,
            'totemStats'       => $totemStats,
            'units'            => $units,
            'selectedUnit'     => $selectedUnit,
        ]);
    }
}
