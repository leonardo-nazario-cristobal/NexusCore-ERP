<?php

declare (strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';
require_once __DIR__ . '/../utils/roleMiddleware.php';

class UserController {

   private Usuario $usuarioModel;

   public function __construct(PDO $connection) {
      $this->usuarioModel = new Usuario($connection);
   }

   /* Listar Usuarios solo el ADMIN */

   public function index(): void {

      RoleMiddleware::allow(['admin']);

      $users = $this->usuarioModel->all();

      Response::ok($users, "Lista De Usuarios.");
   }

   /* Obtener Usuarios por ID */

   public function show(int $id): void {

      RoleMiddleware::allow(['admin']);

      if ($id <= 0) {
         Response::badRequest("ID Invalido.");
      }

      $user = $this->usuarioModel->findById($id);

      if (!$user) {
         Response::notFound("Usuario No Encontrado.");
      }

      Response::ok($user);
   }

   /* Crear usuario */

   public function store(): void {

      RoleMiddleware::allow(['admin']);

      $input = $this->getJsonInput();
      
      if (
         empty($input['nombre']) ||
         empty($input['correo']) ||
         empty($input['password'])
      ) {
         Response::validationError(null, "Campos Incompletos.");
      }

      if (!filter_var($input['correo'], FILTER_VALIDATE_EMAIL)) {
         Response::validationError(null, "Correo Invalido.");
      }

      $rol = $input['rol'] ?? 'cajero';

      try {
         $user = $this->usuarioModel->create(
            trim($input['nombre']),
            strtolower(trim($input['correo'])),
            $input['password'],
            $rol
         );

         Response::created($user, "Usuario Creado.");

      } catch (RuntimeException $e) {
         Response::conflict($e->getMessage());
      }
   }

   /* Actualizar Usuario */

   public function update(int $id): void {

      RoleMiddleware::allow(['admin']);

      if ($id <= 0) {
         Response::badRequest("ID Invalido.");
      }

      $input = $this->getJsonInput();

      try {

         $update = $this->usuarioModel->update($id, $input);

         if (!$update) {
            Response::notFound("Usuario No Encontrado.");
         }

         Response::ok($update, "Usuario Actualizado.");

      } catch (InvalidArgumentException $e) {

         Response::validationError(null, $e->getMessage());

      } catch (RuntimeException $e) {

         Response::conflict($e->getMessage());

      }
   }

   /* Desactivar Usuario */

   public function destroy(int $id): void {

      $authUser = RoleMiddleware::allow(['admin']);

      if ($id <= 0) {
         Response::badRequest("ID Invaalido.");
      }

      /* No permitir auto-desactivarse */

      if ((int)$authUser['sub'] === $id) {
         Response::error("No Puedes Desactivarte A Ti Mismo.", 400);
      }

      $user = $this->usuarioModel->deactivate($id);

      if (!$user) {
         Response::notFound("Usuario No Encontrado.");
         return;
      }

      Response::ok($user, "Usuario Desactivado.");
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