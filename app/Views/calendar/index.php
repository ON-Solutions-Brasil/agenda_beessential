<?php use App\Core\Auth; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar3 me-2"></i>Calendário</h2>
    <?php if (Auth::hasPermission('meetings.create')): ?>
    <a href="/meetings/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nova Reunião
    </a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div id="fullcalendar"></div>
    </div>
</div>
