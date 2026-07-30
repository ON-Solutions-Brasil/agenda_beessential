<?php
/**
 * Configurações de conexão com o banco de dados.
 */
return [
    'driver'   => 'mysql',
    'host'     => 'localhost',
    'port'     => 3306,
    'database' => 'agenda_beessential',
    'username' => 'agenda_beessential',
    'password' => 'Zcze!bu4URpgj10$',
    'charset'  => 'utf8mb4',
    'collation'=> 'utf8mb4_unicode_ci',
    'prefix'   => '',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
