<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/response.php';

class HealthController {
   public static function  check(){
      try {
         Database::getConnection();
         Response::ok(
            "API y Base de datos funcionando"
         );
      } catch (Throwable $e) {
         Response::serverError(
            "Error en el helath check",
            $e->getMessage()
         );
      }
   }
}