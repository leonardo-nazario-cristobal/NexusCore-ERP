<?php

require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';
require_once __DIR__ . '/../utils/roleMiddleware.php';

class CategoriaController {

   private $catModel;

   public function __construct() {
      $this->catModel = new Categoria();
   }

   // Crear
   public function store() {

      $user = AuthMiddleware::verify();
      RoleMiddleware::allow($user, ['admin']);

      $input = json_decode(file_get_contents("php://input"), true);

      if (!$input || empty($input['nombre'])) {
         Response::validationError(null, "Nombre Obligatorio");
      }

      $cat = $this->catModel->create($input);

      Response::created($cat, "Categoria Creada");
   }

   // Listar
   public function index() {
      AuthMiddleware::verify();
      $cats = $this->catModel->all();
      Response::ok($cats, "Lista Categorias");
   }

   // Mostrar
   public function show($id) {
      $cat = $this->catModel->find($id);

      if (!$cat) {
         Response::notFound("Categoria no Encontrada");
      }

      Response::ok($cat);
   }

   // Actualizar
   public function update($id) {
      $user = AuthMiddleware::verify();
      RoleMiddleware::allow($user, ['admin']);

      $input = json_decode(file_get_contents("php://input"), true);

      if (!$input || empty($input['nombre'])) {
         Response::validationError(null, "Nombre Obligatorio");
      }

      $cat = $this->catModel->update($id, $input);

      if (!$cat) {
         Response::notFound("Categoria no Encontrada");
      }

      Response::ok($cat, "Categoria Actualizada");
   }

   // Eliminar
   public function destroy($id) {

      $user = AuthMiddleware::verify();
      RoleMiddleware::allow($user, ['admin']);

      $rows = $this->catModel->delete($id);

      if ($rows === 0) {
         Response::notFound("Categoria no Encontrada");
      }

      Response::ok(null, "Categoria Eliminado");
   }
}