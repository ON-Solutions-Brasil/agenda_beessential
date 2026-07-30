<?php

namespace App\Core;

/**
 * Helper de renderização de views.
 */
class View
{
    private static string $viewsPath = __DIR__ . '/../Views/';

    /**
     * Renderiza uma view dentro do layout.
     */
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        // Extrai variáveis para ficarem disponíveis na view
        extract($data);

        // Captura o conteúdo da view
        ob_start();
        $viewFile = self::$viewsPath . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<p>View não encontrada: {$view}</p>";
        }
        $content = ob_get_clean();

        // Renderiza o layout com o conteúdo
        $layoutFile = self::$viewsPath . "layouts/{$layout}.php";
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            // Se não há layout, renderiza direto
            echo $content;
        }
    }

    /**
     * Renderiza uma view parcial (sem layout).
     */
    public static function partial(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = self::$viewsPath . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        }
    }

    /**
     * Escapa uma string para HTML.
     */
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Gera URL da aplicação.
     */
    public static function url(string $path = ''): string
    {
        $config = require __DIR__ . '/../../config/app.php';
        $baseUrl = rtrim($config['url'] ?? '', '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Gera URL para assets.
     * Usa caminho absoluto de raiz para funcionar em qualquer domínio/host,
     * já que o .htaccess serve /assets/ diretamente de public/assets/.
     */
    public static function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }

    /**
     * Gera campo hidden de CSRF.
     */
    public static function csrf(): string
    {
        $token = Session::getCsrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . self::escape($token) . '">';
    }
}
