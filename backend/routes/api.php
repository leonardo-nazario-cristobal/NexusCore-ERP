<?php

require_once __DIR__ . '/../controllers/AuthController.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$auth = new AuthController();

if ($path === '/api/register' && $method === 'POST') {
   $auth->register();
}

if ($path === '/api/login' && $method === 'POST') {
   $auth->login();
}
