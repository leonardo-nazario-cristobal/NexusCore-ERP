<?php

declare (strict_types=1);

class Response {

   // Método Base
   private static function send(
      bool $success,
      mixed $data,
      string $message,
      int $status
   ): void {
      http_response_code($status);
      header('Content-Type: application/json');

      echo json_encode([
         "success" => $success,
         "status"  => $status,
         "message" => $message,
         "data"    => $data
      ], JSON_UNESCAPED_UNICODE);
      
      exit;
   }


   /* Respuestas Exitosas */

   public static function ok(

      mixed $data = null,
      string $message = "OK"
      
   ): void {

      self::send(true, $data, $message, 200);

   }

   public static function created(

      mixed $data = null,
      string $message = "Recurso Creado"

   ): void {

      self::send(true, $data, $message, 201);

   }

   
   /* Errores de cliente */
   
   public static function badRequest(

      string $message = "Solicitud Inválida"
      
   ): void {

      self::send(false, null, $message, 400);

   }

   public static function unauthorized(
      
      string $message = "No Autenticado"
      
   ): void {

      self::send(false, null, $message, 401);

   }

   public static function forbidden(
      
      string $message = "Acceso Prohibido"
      
   ): void {

      self::send(false, null, $message, 403);

   }

   public static function notFound(
      
      string $message = "Recurso No Encontrado"
      
   ): void {

      self::send(false, null, $message, 404);

   }

   public static function conflict(
      
      string $message= "Conflicto En La Solicitud"
   
   ): void {

      self::send(false, null, $message, 409);

   }

   public static function validationError(

      mixed $errors,
      string $message = "Error De Validación"

   ): void {

      self::send(false, $errors, $message, 422);

   }

   /* Error servidor */
   public static function serverError(

      string $message = "Error Interno Del Servidor",
      mixed $debug = null

   ): void {

      self::send(false, $debug, $message, 500);

   }

   public static function error(

      string $message,
      int $status = 500,
      mixed $data = null

   ): void {

      self::send(false, $data, $message, $status);
      
   }
}