<?php
/**
 * Front Controller - Ponto de entrada principal da aplicação.
 * Todas as requisições são direcionadas para cá via .htaccess.
 */

// Autoloader simples baseado em namespaces
spl_autoload_register(function (string $class) {
    // Converte namespace para caminho de arquivo
    // App\Core\Router => app/Core/Router.php
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    // Verifica se a classe usa o prefixo App\
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Obtém o caminho relativo da classe
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Inicializa e executa a aplicação
$app = new \App\Core\App();
$app->run();
