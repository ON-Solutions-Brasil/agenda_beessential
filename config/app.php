<?php
/**
 * Configurações gerais da aplicação.
 */
return [
    'name'     => 'Agenda Beessential',
    'url'      => 'http://localhost/agenda_beessential',
    'timezone' => 'America/Sao_Paulo',
    'locale'   => 'pt_BR',
    'charset'  => 'UTF-8',
    'debug'    => true, // Em produção, alterar para false

    // Chave usada para tokens CSRF e hashing interno
    'secret_key' => 'ALTERE_ESTA_CHAVE_EM_PRODUCAO_beessential_2026',

    // Sessão
    'session' => [
        'name'     => 'beessential_session',
        'lifetime' => 7200, // 2 horas em segundos
    ],

    // Upload
    'upload' => [
        'max_size'       => 64 * 1024 * 1024, // 64 MB
        'allowed_types'  => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'path'           => __DIR__ . '/../public/uploads',
    ],

    // Google Meet / Google Calendar API
    'google' => [
        'client_id'     => '',
        'client_secret' => '',
        'redirect_uri'  => '',
        'api_key'       => '',
    ],
];
