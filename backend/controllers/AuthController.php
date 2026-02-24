<?php

declare (strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../utils/response.php';

class AuthController {

   private Usuario $usuarioModel;

   public function __construct(PDO $connection) {
      $this->usuarioModel = new Usuario($connection);
   }

   /* Registro */

   public function register(): void {

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

      try {

         $user = $this->usuarioModel->create(
            trim($input['nombre']),
            strtolower(trim($input['correo'])),
            $input['password']
         );

         Response::created($user, "Usuario Creado.");

      } catch (RuntimeException $e) {

         Response::conflict($e->getMessage());
         
      }
   }

   /* Login */

   public function login(): void {

      $input = $this->getJsonInput();

      if (
         empty($input['correo']) ||
         empty($input['password'])
      ) {
         Response::validationError(null, "Credenciales Invalidas.");
      }

      $user = $this->usuarioModel->findByEmail(
         strtolower(trim($input['correo']))
      );

      if (
         !$user ||
         !$this->usuarioModel->verifyPassword(
            $input['password'],
            $user['password']
         )
      ) {
         Response::unauthorized("Credenciales Incorrectas.");
      }

      /* Bloquear usuarios desactivados */
      
      if (!$user['activo']) {
         Response::forbidden("Usuario Bloqueado.");
      }

      $token = $this->generateJWT($user);

      unset($user['password']);

      Response::ok([
         "token" => $token,
         "user"  => $user
      ], "Login Exitoso.");

   }

   /* Utilidades */

   private function getJsonInput (): array {
      
      $input = json_decode(file_get_contents("php://input"), true);
      
      if (!is_array($input)) {
         Response::badRequest("JSON Invalido.");
      }

      return $input;
   }

   private function base64UrlEncode(string $data): string {
      return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
   }

   private function generateJWT(array $user): string {

      $secret = getenv("JWT_SECRET");

      if (!$secret) {
         throw new RuntimeException("JSON_SECRET No Configurado.");
      }

      $header = $this->base64UrlEncode(json_encode([
         "alg" => "HS256",
         "typ" => "JWT"
      ]));

      $payload = $this->base64UrlEncode(json_encode([
         "sub" => $user['id'],
         "name" => $user['nombre'],
         "rol" => $user['rol'],
         "iat" => time(),
         "exp" => time() + 3600
      ]));

      $signature = hash_hmac(
         'sha256',
         "$header.$payload",
         $secret,
         true
      );

      $signature = $this->base64UrlEncode($signature);

      return "$header.$payload.$signature";
   }
}