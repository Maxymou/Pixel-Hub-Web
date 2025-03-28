<?php

use App\Core\Router;

$router = new Router();

// Routes publiques
$router->get('/', ['App\Controllers\HomeController', 'index']);
$router->get('/login', ['App\Controllers\AuthController', 'showLogin']);
$router->post('/login', ['App\Controllers\AuthController', 'login']);
$router->get('/register', ['App\Controllers\AuthController', 'showRegister']);
$router->post('/register', ['App\Controllers\AuthController', 'register']);
$router->get('/logout', ['App\Controllers\AuthController', 'logout']);

// Routes protégées
$router->middleware(['App\Middleware\AuthMiddleware']);
$router->get('/dashboard', ['App\Controllers\DashboardController', 'index']);
$router->get('/profile', ['App\Controllers\ProfileController', 'show']);
$router->post('/profile', ['App\Controllers\ProfileController', 'update']);

// Routes pour les mises à jour
$router->get('/updates', ['App\Controllers\UpdateViewController', 'index']);

// Routes API
$router->prefix('/api')->group(function ($router) {
    $router->middleware(['App\Middleware\ApiAuthMiddleware']);
    
    // Routes pour les applications
    $router->get('/apps', ['App\Controllers\ApplicationController', 'index']);
    $router->post('/apps', ['App\Controllers\ApplicationController', 'store']);
    $router->get('/apps/{id}', ['App\Controllers\ApplicationController', 'show']);
    $router->put('/apps/{id}', ['App\Controllers\ApplicationController', 'update']);
    $router->delete('/apps/{id}', ['App\Controllers\ApplicationController', 'destroy']);
    $router->post('/apps/{id}/action/{action}', ['App\Controllers\ApplicationController', 'action']);
    $router->get('/apps/{id}/logs', ['App\Controllers\ApplicationController', 'logs']);

    // Routes pour les pixels
    $router->get('/pixels', ['App\Controllers\Api\PixelController', 'index']);
    $router->post('/pixels', ['App\Controllers\Api\PixelController', 'store']);
    $router->get('/pixels/{id}', ['App\Controllers\Api\PixelController', 'show']);
    $router->put('/pixels/{id}', ['App\Controllers\Api\PixelController', 'update']);
    $router->delete('/pixels/{id}', ['App\Controllers\Api\PixelController', 'destroy']);

    // Routes de l'API des icônes
    $router->post('/icons/upload', ['App\Controllers\Api\IconController', 'upload']);
    $router->delete('/icons/{id}', ['App\Controllers\Api\IconController', 'delete']);
    $router->get('/icons', ['App\Controllers\Api\IconController', 'getAll']);

    // Routes pour les sauvegardes
    $router->group('/backups', function($router) {
        $router->get('/', 'BackupController@index');
        $router->post('/', 'BackupController@create');
        $router->get('/{id}', 'BackupController@show');
        $router->post('/{id}/restore', 'BackupController@restore');
        $router->delete('/{id}', 'BackupController@destroy');
        
        // Routes pour les planifications
        $router->post('/schedules', 'BackupController@schedule');
        $router->delete('/schedules/{id}', 'BackupController@deleteSchedule');
        $router->patch('/schedules/{id}/toggle', 'BackupController@toggleSchedule');
    });

    // Routes pour les mises à jour
    $router->group('/updates', function($router) {
        $router->get('/', 'UpdateController@list');
        $router->get('/check', 'UpdateController@check');
        $router->post('/download', 'UpdateController@download');
        $router->post('/install', 'UpdateController@install');
        $router->post('/rollback', 'UpdateController@rollback');
        $router->post('/schedule', 'UpdateController@schedule');
    });
});

// Routes pour les sauvegardes
$router->get('/backups', ['App\Controllers\BackupViewController', 'index']);

// Routes du tableau de bord
$router->get('/api/stats', ['App\Controllers\DashboardController', 'getStats']);
$router->get('/api/apps/recent', ['App\Controllers\DashboardController', 'getRecentApps']);
$router->get('/api/notifications', ['App\Controllers\DashboardController', 'getNotifications']);
$router->get('/api/updates/check', ['App\Controllers\DashboardController', 'checkUpdates']);
$router->post('/api/updates/install/{id}', ['App\Controllers\DashboardController', 'installUpdate']);
$router->get('/api/backups', ['App\Controllers\DashboardController', 'getBackups']);
$router->post('/api/backups/restore/{id}', ['App\Controllers\DashboardController', 'restoreBackup']);
$router->delete('/api/backups/{id}', ['App\Controllers\DashboardController', 'deleteBackup']);

return $router; 