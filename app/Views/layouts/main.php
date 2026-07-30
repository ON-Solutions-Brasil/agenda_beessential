<?php
use App\Core\Auth;
use App\Core\Session;
use App\Core\View;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Beessential</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet">
    <link href="<?= View::asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/dashboard">
                <i class="bi bi-calendar-check me-2"></i>Agenda Beessential
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                    </li>
                    <?php if (Auth::hasPermission('meetings.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/meetings"><i class="bi bi-people me-1"></i>Reuniões</a>
                    </li>
                    <?php endif; ?>
                    <?php if (Auth::hasPermission('calendar.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/calendar"><i class="bi bi-calendar3 me-1"></i>Calendário</a>
                    </li>
                    <?php endif; ?>
                    <?php if (Auth::hasPermission('users.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/users"><i class="bi bi-person-gear me-1"></i>Usuários</a>
                    </li>
                    <?php endif; ?>
                    <?php if (Auth::hasPermission('settings.view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/settings"><i class="bi bi-gear me-1"></i>Configurações</a>
                    </li>
                    <?php endif; ?>
                    <?php if (Auth::isSuperAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/clients"><i class="bi bi-person-lines-fill me-1"></i>Clientes</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-shield-lock me-1"></i>Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin"><i class="bi bi-speedometer me-2"></i>Painel</a></li>
                            <li><a class="dropdown-item" href="/admin/roles"><i class="bi bi-person-badge me-2"></i>Roles</a></li>
                            <li><a class="dropdown-item" href="/admin/permissions"><i class="bi bi-key me-2"></i>Permissões</a></li>
                            <li><a class="dropdown-item" href="/admin/totem"><i class="bi bi-easel me-2"></i>Modo Totem</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= View::escape(Auth::userName() ?? '') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small"><?= View::escape(Auth::userEmail() ?? '') ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo principal -->
    <main class="main-content">
        <div class="container-fluid py-4">
            <!-- Flash messages -->
            <?php if (Session::hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= Session::getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            <?php if (Session::hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= Session::getFlash('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            <?php if (Session::hasFlash('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i><?= Session::getFlash('info') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
    <script src="<?= View::asset('js/app.js') ?>"></script>
</body>
</html>
