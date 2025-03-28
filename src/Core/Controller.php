<?php

namespace App\Core;

abstract class Controller
{
    protected Application $app;
    protected array $config;
    protected Logger $logger;
    protected Database $db;

    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->config = $this->app->getConfig();
        $this->logger = $this->app->getLogger();
        $this->db = $this->app->getDatabase();
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = APP_PATH . '/Views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("Vue non trouvée : {$view}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require APP_PATH . '/Views/layouts/main.php';
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function validateRequest(array $rules): array
    {
        $errors = [];
        $data = [];

        foreach ($rules as $field => $rule) {
            $value = $_POST[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "Le champ {$field} est requis";
                continue;
            }

            if (!empty($value)) {
                if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "Le champ {$field} doit être une adresse email valide";
                }
                
                if (strpos($rule, 'min:') !== false) {
                    preg_match('/min:(\d+)/', $rule, $matches);
                    if (strlen($value) < $matches[1]) {
                        $errors[$field] = "Le champ {$field} doit contenir au moins {$matches[1]} caractères";
                    }
                }
            }

            $data[$field] = $value;
        }

        return [
            'errors' => $errors,
            'data' => $data
        ];
    }

    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }
} 