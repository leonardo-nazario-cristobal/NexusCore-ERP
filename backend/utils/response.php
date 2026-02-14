<?php

class Response {

   // Enviar respuesta JSON estandar
   private static function send(
      bool $success,
      $data,
      string $message,
      int $status
   ) {
      http_response_code($status);

      echo json_encode([
         "success" => $success,
         "status" => $status,
         "message" => $message,
         "data" => $data
      ], JSON_UNESCAPED_UNICODE);
      exit;
   }

   // Alias estándar (muchos controladores usan success)
   public static function success(
      string $message = "OK",
      $data = null
   ) {
      self::send(true, $data, $message, 200);
   }

   // Respuesta exitosa
   public static function ok($data = null, string $message = "OK") {
      self::send(true, $data, $message, 200);
   }

   public static function created($data = null, string $message = "Recurso Creado") {
      self::send(true, $data, $message, 201);
   }

   public static function noContent() {
      http_response_code(204);
      exit;
   }
   
   // Errores de cliente
   public static function badRequest(string $message = "Solicitud Inválida") {
      self::send(false, null, $message, 400);
   }

   public static function unauthorized(string $message = "No Autenticado") {
      self::send(false, null, $message, 401);
   }

   public static function forbidden(string $message = "Acceso Prohibido") {
      self::send(false, null, $message, 403);
   }

   public static function notFound(string $message = "Recurso no Encontrado") {
      self::send(false, null, $message, 404);
   }

   public static function conflict(string $message= "Conflicto en la Solicitud") {
      self::send(false, null, $message, 409);
   }

   public static function validationError(
      $errors,
      string $message = "Error de Validación"
   ) {
      self::send(false, $errors, $message, 422);
   }

   // Error servidor
   public static function serverError(
      string $message = "Error Interno del Servidor",
      $debug = null
   ) {
      $data = null;

      if ($debug !== null) {
         $data = $debug;
      }
      self::send(false, $data, $message, 500);
   }

}