<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../utils/response.php';

class AuthController {

   private $usuarioModel;

   public function __construct() {
      $this->usuarioModel = new Usuario();
   }

   // REGISTRO
   public function register() {

      $input = json_decode(file_get_contents("php://input"), true);

      if (!$input) {
         Response::badRequest("JSON inválido");
      }

      if (
         empty($input['nombre']) ||
         empty($input['correo']) ||
         empty($input['password'])
      ) {
         Response::validationError(null, "Campos incompletos");
      }

      try {

         $user = $this->usuarioModel->create(
            $input['nombre'],
            $input['correo'],
            $input['password']
         );

         Response::created($user, "Usuario creado");

      } catch (PDOException $e) {
         Response::conflict("Correo ya registrado");
      }
   }

   // LOGIN
   public function login() {

      $input = json_decode(file_get_contents("php://input"), true);

      if (!$input) {
         Response::badRequest("JSON inválido");
      }

      if (
         empty($input['correo']) ||
         empty($input['password'])
      ) {
         Response::validationError(null, "Credenciales incompletas");
      }

      $user = $this->usuarioModel->findByEmail($input['correo']);

      if (!$user) {
         Response::unauthorized("Credenciales incorrectas");
      }

      if (!$this->usuarioModel->verifyPassword(
            $input['password'],
            $user['password']
         )) {
         Response::unauthorized("Credenciales incorrectas");
      }

      $token = $this->generateJWT($user);

      unset($user['password']);

      Response::ok([
         "token" => $token,
         "user" => $user
      ], "Login exitoso");
   }

   // GENERAR JWT
   private function base64UrlEncode($data) {
      return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
   }

   private function generateJWT($user) {

      $secret = getenv("JWT_SECRET");

      $header = $this->base64UrlEncode(json_encode([
         "alg" => "HS256",
         "typ" => "JWT"
      ]));

      $payload = $this->base64UrlEncode(json_encode([
         "sub" => $user['id'],
         "name" => $user['nombre'],
         "rol" => $user['rol'],
         "iat" => time(),
         "exp" => time() + 36000
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