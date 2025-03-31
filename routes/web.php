<?php

use App\Core\Router;

$router = new Router();

$router->get('/', 'home');
$router->get('/about', 'about');
$router->get('/contact', 'contact');

$router->get('/login', 'login');
$router->post('/login', 'login');
$router->get('/register', 'register');
$router->post('/register', 'register');
$router->get('/logout', 'logout');

$router->get('/profile', 'profile');
$router->post('/profile', 'profile');
$router->get('/settings', 'settings');
$router->post('/settings', 'settings'); 