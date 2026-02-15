<?php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../utils/authMiddleware.php';
require_once __DIR__ . '/../utils/roleMiddleware.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../utils/response.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$auth = new AuthController();
$userController = new UserController();

// ===== Auth =====

if ($path === '/api/register' && $method === 'POST') {
   $auth->register();
   return;
}

if ($path === '/api/login' && $method === 'POST') {
   $auth->login();
   return;
}

// ===== Private test =====
if ($path === '/api/private-test' && $method === 'GET') {

   $user = AuthMiddleware::verify();

   RoleMiddleware::allow($user, ['admin']);

   Response::ok($user, "Acceso Admin, Bienvenido");
   return;
}

// ===== Usuarios =====
// Listar Usuarios
if ($path === '/api/users' && $method === 'GET') {

   $user = AuthMiddleware::verify();

   RoleMiddleware::allow($user, ['admin']);

   $userController->index();
   return;
}

// Crear Usuarios
if ($path === '/api/users' && $method === 'POST') {
   
   $user = AuthMiddleware::verify();

   RoleMiddleware::allow($user, ['admin']);

   $userController->store();
   return;
}

if (preg_match('#^/api/users/(\d+)$#', $path, $matches)) {

$user = AuthMiddleware::verify();

   RoleMiddleware::allow($user, ['admin']);
   
   if ($method === 'GET') {
      $userController->show($matches[1]);
      return;
   }

   if ($method === 'PUT') {
      $userController->update($matches[1]);
      return;
   }

   if ($method === 'DELETE') {
      $userController->destroy($matches[1]);
      return;
   }
}

// Not Found
Response::notFound("Ruta no Encontrada");
