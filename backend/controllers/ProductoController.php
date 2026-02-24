<?php

declare (strict_types=1);

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';
require_once __DIR__ . '/../utils/roleMiddleware.php';

class ProductoController {

   private Producto $productoModel;

   public function __construct(PDO $connection) {
      $this->productoModel = new Producto($connection);
   }

   /* Crear Producto */
   
   public function store(): void {

      RoleMiddleware::allow(['admin']);

      $input = $this->getJsonInput();

      if (empty(trim($input['nombre'] ?? '' ))) {
         Response::validationError(null, "Nombre Es Obligatorio.");
      }

      if (!isset($input['precio']) || $input['precio'] < 0) {
         Response::validationError(null, "Precio Invalido.");
      }

      if (isset($input['stock']) && $input['stock'] < 0) {
         Response::validationError(null, "Stock Invalido.");
      }

      try {

         $producto = $this->productoModel->create($input);
         Response::created($producto, "Producto Creado.");

      } catch (RuntimeException $e) {

         Response::conflict($e->getMessage());

      }
   }

   /* Listar Productos */

   public function index(): void {

      AuthMiddleware::verify();

      $productos = $this->productoModel->all();

      Response::ok($productos, "Lista De Productos Con Categoria.");
   }

   /* Buscar por ID */

   public function show(int $id): void {

      AuthMiddleware::verify();

      $producto = $this->productoModel->find($id);

      if (!$producto) {
         Response::notFound("Producto No Encontrado.");
      }

      Response::ok($producto, "Detalle Del Producto.");
   }

   /* Actualizar productos */

   public function update(int $id): void {

      RoleMiddleware::allow(['admin']);

      $input = $this->getJsonInput();

      $updated = $this->productoModel->update($id, $input);

      if (!$updated) {
         Response::notFound("Producto No Encontrado O Sin Cambios.");
      }

      Response::ok($updated, "Producto Actualizado.");
   }

   /* Delete Procucto */

   public function destroy(int $id): void {

      RoleMiddleware::allow(['admin']);

      $producto = $this->productoModel->deactivate($id);

      if (!$producto) {
         Response::notFound("Producto No Encontrado.");
      }

      Response::ok($producto, "Producto Desactivado.");
   }

   /* Utilidades */

   private function getJsonInput(): array {

      $input = json_decode(file_get_contents("php://input"), true);

      if (!is_array($input)) {
         Response::badRequest("JSON Invalido.");
      }

      return $input;
   }
}