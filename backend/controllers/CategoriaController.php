<?php

declare (strict_types=1);

require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';
require_once __DIR__ . '/../utils/roleMiddleware.php';

class CategoriaController {

   private Categoria $catModel;

   public function __construct(PDO $connection) {
      $this->catModel = new Categoria($connection);
   }

   /* Crear */

   public function store(): void {

      RoleMiddleware::allow(['admin']);

      $input = $this->getJsonInput();

      if (empty(trim($input['nombre'] ?? '' ))) {
         Response::validationError(null, "Nombre Obligatorio.");
      }

      $cat = $this->catModel->create($input);

      Response::created($cat, "Categoria Creada.");
   }

   /* Listar */

   public function index(): void {

      AuthMiddleware::verify();

      $cats = $this->catModel->all();

      Response::ok($cats, "Lista Categorias.");
   }

   /* Buscar por ID */

   public function show(int $id) {

      $cat = $this->catModel->find($id);

      if (!$cat) {
         Response::notFound("Categoria no Encontrada.");
      }

      Response::ok($cat);
   }

   /* Actualizar */

   public function update(int $id): void {

      RoleMiddleware::allow(['admin']);

      $input = $this->getJsonInput();

      if (empty(trim($input['nombre'] ?? '' ))) {
         Response::validationError(null, "Nombre Obligatorio.");
      }

      $cat = $this->catModel->update($id, $input);

      if (!$cat) {
         Response::notFound("Categoria No Encontrada.");
      }

      Response::ok($cat, "Categoria Actualizada.");
   }

   /* Eliminar */

   public function destroy(int $id): void {

      RoleMiddleware::allow(['admin']);

      $delete = $this->catModel->delete($id);

      if (!$delete) {
         Response::notFound("Categoria No Encontrada.");
      }

      Response::ok(null, "Categoria Eliminado.");
   }

   /* Utilidades */

   private function getJsonInput() : array {

      $input = json_decode(file_get_contents("php://input"), true);

      if (!is_array($input)) {
         Response::validationError(null, "JSON Invalido.");
      }

      return $input;
   }
}