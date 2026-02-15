<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';
require_once __DIR__ . '/../utils/roleMiddleware.php';

class UserController {

   private $usuarioModel;

   public function __construct() {
      $this->usuarioModel = new Usuario();
   }

   // Listar Usuarios solo el ADMIN
   public function index() {

      $user = AuthMiddleware::verify();
      RoleMiddleware::allow($user, ['admin']);

      $users = $this->usuarioModel->all();

      Response::ok($users, "Lista de Usuarios");
   }

   // Obtener Usuarios por ID
   public function show($id) {

      $user = AuthMiddleware::verify();
      RoleMiddleware::allow($user, ['admin']);

      $userData = $this->usuarioModel->findById($id);

      if (!$userData) {
         Response::notFound("Usuario no Encontrado");
      }

      Response::ok($userData);
   }

   // Crear usuario
   public function store() {

      $user = AuthMiddleware::verify();
      RoleMiddleware::allow($user, ['admin']);

      $input = json_decode(file_get_contents("php://input"), true);

      if (
         empty($input['nombre']) ||
         empty($input['correo']) ||
         empty($input['password'])
      ) {
         Response::validationError(null, "Campos Incompletos");
      }

      $rol = $input['rol'] ?? 'cajero';
      try {
         $newUser = $this->usuarioModel->crate(
            $input['nombre'],
            $input['correo'],
            $input['password'],
            $rol
         );

         Response::created($newUser, "Usuario Creado");

      } catch (PDOException $e) {
         Response::conflic("Correo Duplicado");
      }
   }

   // Update Usuario
   public function update($id) {

      $user = AuthMiddleware::verify();
      RoleMiddleware::allow($user, ['admin']);

      $input = json_decode(file_get_contents("php://input"), true);

      if (!$input) {
         Response::badRequest("JSON Invalido");
      }

      $updated = $this->usuarioModel->update($id, $input);

      if (!$updated) {
         Response::badRequest("Nada que Actualizar u Usuario no Encontrado");
      }

      Response::ok($updated, "Usuario Actualizado");
   }

   // Delete Usuario
   public function destroy($id) {

      $user = AuthMiddleware::verify();
      RoleMiddleware::allow($user, ['admin']);

      // No permitir auto-desactivarse
      if ($user['sub'] == $id) {
         Response::error("No puedes desactivarte a ti mismo", 400);
         return;
      }

      $updatedUser = $this->usuarioModel->deactivate($id);

      if (!$updatedUser) {
         Response::notFound("Usuario no Encontrado");
         return;
      }

      Response::ok($updatedUser, "Usuario Desactivado");
   }

}