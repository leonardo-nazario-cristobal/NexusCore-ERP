<?php

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';
require_once __DIR__ . '/../utils/roleMiddleware.php';

class ProductoController {

   private $productoModel;

   public function __construct() {
      $this->productoModel = new Producto();
   }

   // Crear Producto
   public function store() {
      $user = AuthMiddleware::verify();
      RoleMiddleware::allow($user, ['admin']);
      $input = json_decode(file_get_contents("php://input"), true);

      if (!$input) {
         Response::badRequest("JSON Invalido");
      }

      if (
         empty($input['nombre']) ||
         !isset($input['precio'])
      ) {
         Response::validationError(null, "Nombre y Precio son Obligatorios");
      }

      try {
         $producto = $this->productoModel->create($input);

         Response::created($producto, "Producto Creado");
      } catch (PDOException $e) {
         Response::serverError("Error al Crear Producto", $e->getMessage());
      }
   }

   // Listar Productos
   public function index() {

      $user = AuthMiddleware::verify();

      $productos = $this->productoModel->all();

      Response::ok($productos, "Lista de Productos");
   }

   // Update productos
   public function update($id) {

      $user = AuthMiddleware::verify();
      RoleMiddleware::allow($user, ['admin']);

      $input = json_decode(file_get_contents("php://input"), true);

      if (!$input) {
         Response::badRequest("JSON Invalido");
      }
      
      $update = $this->productoModel->update($id, $input);

      if (!$update) {
         Response::badRequest("Nada que Actualizar u Producto no Encontrado");
      }

      Response::ok($update, "Producto Modificado");
   }

   // Delete Procucto
   public function destroy($id) {

      $user = AuthMiddleware::verify();
      RoleMiddleware::allow($user, ['admin']);

      // No permitir auto-desactivarse
      // if ($user['sub'] == $id) {
      //    Response::error("No puedes desactivarte a ti mismo", 400);
      //    return;
      // }

      $updatedProducto = $this->productoModel->deactivate($id);

      if (!$updatedProducto) {
         Response::notFound("Producto no Encontrado");
         return;
      }

      Response::ok($updatedProducto, "Producto Desactivado");
   }
}