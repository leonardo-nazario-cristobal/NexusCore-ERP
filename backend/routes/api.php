<?php

declare (strict_types=1);

require_once __DIR__ . '/../config/database.php';
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

$pdo = Database::getConnection();

$auth = new AuthController($pdo);
$userController = new UserController($pdo);
$catController = new CategoriaController($pdo);
$provController = new ProveedorController($pdo);
$producController = new ProductoController($pdo);
$compController = new CompraController($pdo);
$ventController = new VentaController($pdo);
$movController = new MovimientoInventarioController($pdo);

/* ===== Auth ===== */

/* Registrar */

if ($path === '/api/registrar' && $method === 'POST') {
   $auth->register();
   return;
}

/* login */

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

/* ===== Usuarios ===== */

/* Listar Usuarios */

if ($path === '/api/usuarios' && $method === 'GET') {

   $userController->index();
   return;

}

/* Crear Usuarios */

if ($path === '/api/usuarios' && $method === 'POST') {

   $userController->store();
   return;

}

if (preg_match('#^/api/usuarios/(\d+)$#', $path, $matches)) {
   
   $id = (int) $matches[1];

   /* buscar por ID */

   if ($method === 'GET') {
      $userController->show($id);
      return;
   }

   /* Actualizar */

   if ($method === 'PUT') {
      $userController->update($id);
      return;
   }

   /* Bloquear */

   if ($method === 'DELETE') {
      $userController->destroy($id);
      return;
   }
}

/* ===== Categorias ===== */

/* Crear */

if ($path === '/api/categorias' && $method === 'POST') {
   $catController->store();
   return;
}

/* Listar */

if ($path === '/api/categorias' && $method === 'GET') {
   $catController->index();
   return;
}

if (preg_match('#^/api/categorias/(\d+)$#', $path, $matches)) {

$id = (int) $matches[1];

   /* Buscar por ID */

   if ($method === 'GET') {
      $catController->show($id);
      return;
   }

   /* Actualizar */

   if ($method === 'PUT') {
      $catController->update($id);
      return;
   }

   /* Eliminar */

   if ($method === 'DELETE') {
      $catController->destroy($id);
      return;
   }
}

/* ===== Proveedores ===== */

/* Crear */

if ($path === '/api/proveedores' && $method === 'POST') {
   $provController->store();
   return;
}

/* Listar */

if ($path === '/api/proveedores' && $method === 'GET') {
   $provController->index();
   return;
}

if (preg_match('#^/api/proveedores/(\d+)$#', $path, $matches)) {

   $id = (int) $matches[1];

   /* Buscar por ID*/

   if ($method === 'GET') {
      $provController->show($id);
   }

   /* Actualizar */
   
   if ($method === 'PUT') {
      $provController->update($id);
   }

   /* Eliminar */

   if ($method === 'DELETE') {
      $provController->destroy($id);
   }
}

/* ===== Productos ===== */

/* Crear Productos */

if ($path === '/api/productos' && $method === 'POST') {
   $producController->store();
   return;
}

/* Listar Productos */

if ($path === '/api/productos' && $method === 'GET') {
   $producController->index();
   return;
}

if (preg_match('#^/api/productos/(\d+)$#', $path, $matches)) {

$id = (int) $matches[1];

   /* Buscar por ID */

   if ($method === 'GET') {
      $producController->show($id);
   }

   /* Actualizar */

   if ($method === 'PUT') {
      $producController->update($id);
      return;
   }

   /* Desactivar */

   if ($method === 'DELETE') {
      $producController->destroy($id);
      return;
   }
}

/* ===== Compras ===== */

/* crear */

if ($path === '/api/compras' && $method === 'POST') {
   $compController->store();
   return;
}

/* Listar compras */

if ($path === '/api/compras' && $method === 'GET') {
   $compController->index();
   return;
}

if (preg_match('#^/api/compras/(\d+)$#', $path, $matches)) {

   $id = (int) $matches[1];

   /* Buscar por ID */

   if ($method === 'GET') {
      $compController->show($id);
      return;
   }

}

/* ===== Ventas ===== */

/* Crear Venta */

if ($path === '/api/ventas' && $method === 'POST') {
   $ventController->store();
   return;
}

/* Listar Venta */

if ($path === '/api/ventas' && $method === 'GET') {
   $ventController->index();
   return;
}

/* Detalle de Venta */

if (preg_match('#^/api/ventas/(\d+)$#', $path, $matches)) {

   $id = (int) $matches[1];

   /* Buscar por ID */

   if ($method === 'GET') {
      $ventController->show($id);
      return;
   }
}

/* ===== Movimientos Inventario ===== */

/* Crear Movimiento */

if ($path === '/api/inventario' && $method === 'POST') {
   $movController->store();
   return;
}

/* Listar Historial */

if ($path === '/api/inventario' && $method === 'GET') {
   $movController->index();
   return;
}

// Not Found
Response::notFound("Ruta no Encontrada.");