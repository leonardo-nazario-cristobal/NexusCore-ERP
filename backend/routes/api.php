<?php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../utils/authMiddleware.php';
require_once __DIR__ . '/../utils/roleMiddleware.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/ProductoController.php';
require_once __DIR__ . '/../controllers/CategoriaController.php';
require_once __DIR__ . '/../controllers/MovimientoInventarioController.php';
require_once __DIR__ . '/../controllers/CompraController.php';
require_once __DIR__ . '/../controllers/VentaController.php';
require_once __DIR__ . '/../controllers/ProveedorController.php';
require_once __DIR__ . '/../utils/response.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$auth = new AuthController();
$userController = new UserController();
$productoController = new ProductoController();
$catController = new CategoriaController();
$movController = new MovimientoInventarioController();
$compController = new CompraController();
$ventController = new VentaController();
$provController = new ProveedorController();

// ===== Auth =====

// Registrar
if ($path === '/api/register' && $method === 'POST') {
   $auth->register();
   return;
}

// login
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
   
   // buscar por ID
   if ($method === 'GET') {
      $userController->show($matches[1]);
      return;
   }

   // Update
   if ($method === 'PUT') {
      $userController->update($matches[1]);
      return;
   }

   // Delete
   if ($method === 'DELETE') {
      $userController->destroy($matches[1]);
      return;
   }
}

// ===== Productos =====

// Crear Productos
if ($path === '/api/products' && $method === 'POST') {

   $user = AuthMiddleware::verify();

   RoleMiddleware::allow($user, ['admin']);
   $productoController->store();
   return;
}

// Listar Productos
if ($path === '/api/products' && $method === 'GET') {
   $productoController->index();
   return;
}

if (preg_match('#^/api/products/(\d+)$#', $path, $matches)) {

   $user = AuthMiddleware::verify();

   RoleMiddleware::allow($user, ['admin']);

   if ($method === 'PUT') {
      $productoController->update($matches[1]);
      return;
   }

   if ($method === 'DELETE') {
      $productoController->destroy($matches[1]);
      return;
   }
}

// ===== Categorias =====

// Crear
if ($path === '/api/categories' && $method === 'POST') {
   $catController->store();
   return;
}

// Listar
if ($path === '/api/categories' && $method === 'GET') {
   $catController->index();
   return;
}

// ID
if (preg_match('#^/api/categories/(\d+)$#', $path, $matches)) {

   if ($method === 'GET') {
      $catController->show($matches[1]);
      return;
   }

   if ($method === 'PUT') {
      $catController->update($matches[1]);
      return;
   }

   if ($method === 'DELETE') {
      $catController->destroy($matches[1]);
      return;
   }
}

// ===== Movimientos Inventario =====

// Crear Movimiento
if ($path === '/api/inventory-movements' && $method === 'POST') {
   $movController->store();
   return;
}

// Listar Historial
if ($path === '/api/inventory-movements' && $method === 'GET') {
   $movController->index();
   return;
}

// ===== Compras =====

// crear
if ($path === '/api/purchases' && $method === 'POST') {
   $compController->store();
   return;
}

// Listar compras
if ($path === '/api/purchases' && $method === 'GET') {
   $compController->index();
   return;
}

// ===== Ventas =====

// Crear Venta
if ($path === '/api/sales' && $method === 'POST') {
   $ventController->store();
   return;
}

// Listar Venta
if ($path === '/api/sales' && $method === 'GET') {
   $ventController->index();
   return;
}

// Detalle de Venta
if (preg_match('#^/api/sales/(\d+)$#', $path, $matches)) {

   if ($method === 'GET') {
      $ventController->show($matches[1]);
      return;
   }
}

// ===== Proveedores =====

// Crear
if ($path === '/api/providers' && $method === 'POST') {
   $provController->store();
   return;
}

if ($path === '/api/providers' && $method === 'GET') {
   $provController->index();
   return;
}

if (preg_match('#^/api/providers/(\d+)$#', $path, $matches)) {
   if ($method === 'GET') {
      $provController->show($matches[1]);
   }
}

// Not Found
Response::notFound("Ruta no Encontrada");
