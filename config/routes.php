<?php
/**
 * Definição de rotas da aplicação.
 *
 * Formato: 'METHOD /uri' => [Controller::class, 'method']
 * Parâmetros dinâmicos: {id}, {slug}, etc.
 */

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\MeetingController;
use App\Controllers\CalendarController;
use App\Controllers\SettingsController;
use App\Controllers\AdminController;
use App\Controllers\TotemController;
use App\Controllers\TotemAdminController;
use App\Controllers\ClientController;
use App\Controllers\AccountController;

return [
    // ─── Autenticação ───────────────────────────────────────────
    'GET  /login'    => [AuthController::class, 'showLogin'],
    'POST /login'    => [AuthController::class, 'login'],
    'GET  /logout'   => [AuthController::class, 'logout'],

    // ─── Totem (acesso por PIN, sem login) ──────────────────────
    'GET  /totem/pin'    => [TotemController::class, 'pin'],
    'POST /totem/pin'    => [TotemController::class, 'verifyPin'],
    'GET  /totem/exit'   => [TotemController::class, 'exit'],
    'GET  /totem'        => [TotemController::class, 'index'],
    'GET  /totem/rooms'  => [TotemController::class, 'rooms'],
    'POST /totem/reserve'=> [TotemController::class, 'reserve'],
    'POST /totem/reservation/detail' => [TotemController::class, 'reservationDetail'],
    'POST /totem/reservation/update' => [TotemController::class, 'updateReservation'],

    // ─── Dashboard ──────────────────────────────────────────────
    'GET  /'          => [DashboardController::class, 'index'],
    'GET  /dashboard' => [DashboardController::class, 'index'],

    // ─── Reuniões ───────────────────────────────────────────────
    'GET  /meetings'            => [MeetingController::class, 'index'],
    'GET  /meetings/create'     => [MeetingController::class, 'create'],
    'POST /meetings/store'      => [MeetingController::class, 'store'],
    'GET  /meetings/{id}'       => [MeetingController::class, 'show'],
    'GET  /meetings/{id}/edit'  => [MeetingController::class, 'edit'],
    'POST /meetings/{id}/update'=> [MeetingController::class, 'update'],
    'POST /meetings/{id}/cancel'=> [MeetingController::class, 'cancel'],
    'POST /meetings/{id}/delete'=> [MeetingController::class, 'delete'],

    // ─── Calendário ─────────────────────────────────────────────
    'GET  /calendar'       => [CalendarController::class, 'index'],
    'GET  /calendar/events'=> [CalendarController::class, 'events'],

    // ─── Usuários ───────────────────────────────────────────────
    'GET  /users'             => [UserController::class, 'index'],
    'GET  /users/create'      => [UserController::class, 'create'],
    'POST /users/store'       => [UserController::class, 'store'],
    'GET  /users/{id}/edit'   => [UserController::class, 'edit'],
    'POST /users/{id}/update' => [UserController::class, 'update'],
    'POST /users/{id}/delete' => [UserController::class, 'delete'],

    // ─── Configurações ──────────────────────────────────────────
    'GET  /settings'      => [SettingsController::class, 'index'],
    'POST /settings/save' => [SettingsController::class, 'save'],

    // ─── Admin (Superadmin) ─────────────────────────────────────
    'GET  /admin'                     => [AdminController::class, 'index'],
    'GET  /admin/roles'               => [AdminController::class, 'roles'],
    'GET  /admin/roles/create'        => [AdminController::class, 'createRole'],
    'POST /admin/roles/store'         => [AdminController::class, 'storeRole'],
    'GET  /admin/roles/{id}/edit'     => [AdminController::class, 'editRole'],
    'POST /admin/roles/{id}/update'   => [AdminController::class, 'updateRole'],
    'POST /admin/roles/{id}/delete'   => [AdminController::class, 'deleteRole'],
    'GET  /admin/permissions'         => [AdminController::class, 'permissions'],
    'POST /admin/permissions/sync'    => [AdminController::class, 'syncPermissions'],

    // ─── Clientes (histórico) ───────────────────────────────────
    'GET  /clients' => [ClientController::class, 'index'],

    // ─── Conta do usuário ───────────────────────────────────────
    'GET  /account/password'  => [AccountController::class, 'password'],
    'POST /account/password'  => [AccountController::class, 'updatePassword'],

    // ─── Admin: Modo Totem ──────────────────────────────────────
    'GET  /admin/totem'                => [TotemAdminController::class, 'index'],
    'POST /admin/totem/save'           => [TotemAdminController::class, 'save'],
    'POST /admin/totem/rooms/store'    => [TotemAdminController::class, 'storeRoom'],
    'POST /admin/totem/rooms/{id}/update' => [TotemAdminController::class, 'updateRoom'],
    'POST /admin/totem/rooms/{id}/delete' => [TotemAdminController::class, 'deleteRoom'],
    'GET  /admin/totem/rooms/{id}/items'  => [TotemAdminController::class, 'items'],
    'POST /admin/totem/items/store'       => [TotemAdminController::class, 'storeItem'],
    'POST /admin/totem/items/{id}/update' => [TotemAdminController::class, 'updateItem'],
    'POST /admin/totem/items/{id}/delete' => [TotemAdminController::class, 'deleteItem'],
    'POST /admin/totem/sellers/store'       => [TotemAdminController::class, 'storeSeller'],
    'POST /admin/totem/sellers/{id}/update' => [TotemAdminController::class, 'updateSeller'],
    'POST /admin/totem/sellers/{id}/delete' => [TotemAdminController::class, 'deleteSeller'],
    'POST /admin/totem/units/store'         => [TotemAdminController::class, 'storeUnit'],
    'POST /admin/totem/units/{id}/update'   => [TotemAdminController::class, 'updateUnit'],
    'POST /admin/totem/units/{id}/delete'   => [TotemAdminController::class, 'deleteUnit'],
    'POST /admin/totem/units/clone-rooms'   => [TotemAdminController::class, 'cloneRooms'],
    'GET  /admin/totem/logs'                => [TotemAdminController::class, 'logs'],
    'POST /admin/totem/logs/{id}/resend'    => [TotemAdminController::class, 'resendLog'],
    'POST /admin/totem/logs/test'           => [TotemAdminController::class, 'testEmail'],
    'GET  /admin/totem/reservations'        => [TotemAdminController::class, 'reservations'],
    'GET  /admin/totem/reservations/{id}'   => [TotemAdminController::class, 'reservationInfo'],
    'POST /admin/totem/reservations/{id}/update' => [TotemAdminController::class, 'updateReservationAdmin'],
    'POST /admin/totem/reservations/{id}/cancel' => [TotemAdminController::class, 'cancelReservationAdmin'],
    'POST /admin/totem/reservations/{id}/delete' => [TotemAdminController::class, 'deleteReservationAdmin'],
    'GET  /admin/totem/audit'               => [TotemAdminController::class, 'auditLogs'],
];
