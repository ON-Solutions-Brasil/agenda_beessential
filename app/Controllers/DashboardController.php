<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Meeting;
use App\Models\User;

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

        $this->view('dashboard/index', [
            'upcomingMeetings' => $upcomingMeetings,
            'todayMeetings'    => $todayMeetings,
            'stats'            => $stats,
        ]);
    }
}
