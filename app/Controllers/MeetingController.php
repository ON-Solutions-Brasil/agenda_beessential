<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Meeting;
use App\Models\User;
use App\Models\Setting;
use App\Models\ActivityLog;
use App\Services\GoogleMeetService;

class MeetingController extends Controller
{
    private Meeting $meetingModel;
    private User $userModel;

    public function __construct()
    {
        $this->meetingModel = new Meeting();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $this->requirePermission('meetings.view');

        // Admin vê todas, usuário comum vê só as suas
        if (Auth::hasPermission('users.view')) {
            $meetings = $this->meetingModel->allWithOrganizer();
        } else {
            $meetings = $this->meetingModel->getByUser(Auth::userId());
        }

        $this->view('meetings/index', [
            'meetings' => $meetings,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('meetings.create');

        $users = $this->userModel->getActiveUsers();
        $settingModel = new Setting();

        $this->view('meetings/create', [
            'users'           => $users,
            'defaultDuration' => $settingModel->getValue('meeting_default_duration', 60),
            'workStart'       => $settingModel->getValue('work_start_time', '08:00'),
            'workEnd'         => $settingModel->getValue('work_end_time', '18:00'),
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('meetings.create');

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/meetings/create');
            return;
        }

        $data = [
            'title'        => trim($this->input('title', '')),
            'description'  => trim($this->input('description', '')),
            'meeting_date' => $this->input('meeting_date', ''),
            'start_time'   => $this->input('start_time', ''),
            'end_time'     => $this->input('end_time', ''),
            'location'     => trim($this->input('location', '')),
            'color'        => $this->input('color', '#3788d8'),
            'notes'        => trim($this->input('notes', '')),
            'organizer_id' => Auth::userId(),
        ];

        $participants = $this->input('participants', []);
        if (!is_array($participants)) {
            $participants = [];
        }

        // Validações
        if (empty($data['title']) || empty($data['meeting_date']) || empty($data['start_time']) || empty($data['end_time'])) {
            Session::flash('error', 'Título, data, hora início e hora fim são obrigatórios.');
            $this->redirect('/meetings/create');
            return;
        }

        if ($data['start_time'] >= $data['end_time']) {
            Session::flash('error', 'O horário de fim deve ser posterior ao horário de início.');
            $this->redirect('/meetings/create');
            return;
        }

        // Verificar conflito de horário para o organizador
        if ($this->meetingModel->hasConflict($data['meeting_date'], $data['start_time'], $data['end_time'], Auth::userId())) {
            Session::flash('error', 'Você já tem uma reunião agendada neste horário.');
            $this->redirect('/meetings/create');
            return;
        }

        // Gerar link do Google Meet se configurado
        $settingModel = new Setting();
        if ($settingModel->getValue('meeting_auto_generate_meet', true)) {
            $meetService = new GoogleMeetService();
            $data['meet_link'] = $meetService->generateMeetLink($data['title'], $data['meeting_date'], $data['start_time'], $data['end_time']);
        }

        $meetingId = $this->meetingModel->create($data);

        // Adiciona participantes
        if (!empty($participants)) {
            $this->meetingModel->addParticipants($meetingId, $participants);
        }

        $log = new ActivityLog();
        $log->log('meeting.created', 'meeting', $meetingId, "Reunião '{$data['title']}' criada");

        Session::flash('success', 'Reunião agendada com sucesso!');
        $this->redirect('/meetings');
    }

    public function show(string $id): void
    {
        $this->requirePermission('meetings.view');

        $meeting = $this->meetingModel->find((int) $id);
        if (!$meeting) {
            Session::flash('error', 'Reunião não encontrada.');
            $this->redirect('/meetings');
            return;
        }

        $participants = $this->meetingModel->getParticipants((int) $id);

        $this->view('meetings/show', [
            'meeting'      => $meeting,
            'participants' => $participants,
        ]);
    }

    public function edit(string $id): void
    {
        $this->requirePermission('meetings.edit');

        $meeting = $this->meetingModel->find((int) $id);
        if (!$meeting) {
            Session::flash('error', 'Reunião não encontrada.');
            $this->redirect('/meetings');
            return;
        }

        $users = $this->userModel->getActiveUsers();
        $participants = $this->meetingModel->getParticipants((int) $id);
        $participantIds = array_map(fn($p) => $p->id, $participants);

        $this->view('meetings/edit', [
            'meeting'        => $meeting,
            'users'          => $users,
            'participantIds' => $participantIds,
        ]);
    }

    public function update(string $id): void
    {
        $this->requirePermission('meetings.edit');

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect("/meetings/{$id}/edit");
            return;
        }

        $meeting = $this->meetingModel->find((int) $id);
        if (!$meeting) {
            Session::flash('error', 'Reunião não encontrada.');
            $this->redirect('/meetings');
            return;
        }

        $data = [
            'title'        => trim($this->input('title', '')),
            'description'  => trim($this->input('description', '')),
            'meeting_date' => $this->input('meeting_date', ''),
            'start_time'   => $this->input('start_time', ''),
            'end_time'     => $this->input('end_time', ''),
            'location'     => trim($this->input('location', '')),
            'color'        => $this->input('color', '#3788d8'),
            'notes'        => trim($this->input('notes', '')),
            'status'       => $this->input('status', $meeting->status),
        ];

        $participants = $this->input('participants', []);
        if (!is_array($participants)) {
            $participants = [];
        }

        // Validações
        if (empty($data['title']) || empty($data['meeting_date']) || empty($data['start_time']) || empty($data['end_time'])) {
            Session::flash('error', 'Título, data, hora início e hora fim são obrigatórios.');
            $this->redirect("/meetings/{$id}/edit");
            return;
        }

        if ($data['start_time'] >= $data['end_time']) {
            Session::flash('error', 'O horário de fim deve ser posterior ao horário de início.');
            $this->redirect("/meetings/{$id}/edit");
            return;
        }

        // Verificar conflito
        if ($this->meetingModel->hasConflict($data['meeting_date'], $data['start_time'], $data['end_time'], $meeting->organizer_id, (int) $id)) {
            Session::flash('error', 'Conflito de horário com outra reunião.');
            $this->redirect("/meetings/{$id}/edit");
            return;
        }

        $this->meetingModel->update((int) $id, $data);

        // Atualiza participantes
        $this->meetingModel->clearParticipants((int) $id);
        if (!empty($participants)) {
            $this->meetingModel->addParticipants((int) $id, $participants);
        }

        $log = new ActivityLog();
        $log->log('meeting.updated', 'meeting', (int) $id, "Reunião '{$data['title']}' atualizada");

        Session::flash('success', 'Reunião atualizada com sucesso!');
        $this->redirect('/meetings');
    }

    public function cancel(string $id): void
    {
        $this->requirePermission('meetings.cancel');

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/meetings');
            return;
        }

        $meeting = $this->meetingModel->find((int) $id);
        if (!$meeting) {
            Session::flash('error', 'Reunião não encontrada.');
            $this->redirect('/meetings');
            return;
        }

        $this->meetingModel->update((int) $id, ['status' => 'cancelled']);

        $log = new ActivityLog();
        $log->log('meeting.cancelled', 'meeting', (int) $id, "Reunião '{$meeting->title}' cancelada");

        Session::flash('success', 'Reunião cancelada.');
        $this->redirect('/meetings');
    }

    public function delete(string $id): void
    {
        $this->requirePermission('meetings.delete');

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/meetings');
            return;
        }

        $meeting = $this->meetingModel->find((int) $id);
        if (!$meeting) {
            Session::flash('error', 'Reunião não encontrada.');
            $this->redirect('/meetings');
            return;
        }

        $this->meetingModel->delete((int) $id);

        $log = new ActivityLog();
        $log->log('meeting.deleted', 'meeting', (int) $id, "Reunião '{$meeting->title}' excluída");

        Session::flash('success', 'Reunião excluída permanentemente.');
        $this->redirect('/meetings');
    }
}
