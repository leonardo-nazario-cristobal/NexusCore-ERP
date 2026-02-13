<?php

class Response {

   public static function json($data, int $status = 200) {
      http_response_code($status);

      echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
      exit;
   }

   public static function success($data = null, string $message = "OK") {
      self::json([
         "success" => true,
         "message" => $message,
         "data" => $data
      ]);
   }

   public static function error(string $message = "Error", int $status = 400) {
      self::json([
         "success" => false,
         "message" => $message
      ], $status);
   }
}