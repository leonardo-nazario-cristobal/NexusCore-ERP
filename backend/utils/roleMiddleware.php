<?php

require_once __DIR__ . '/response.php';

class RoleMiddleware {

   public static function allow($user, array $rolesPermitidos) {

      if (!isset($user['rol'])) {
         Response::unauthorized("Rol no Encontrado");
      }

      if (!in_array($user['rol'], $rolesPermitidos)) {
         Response::forbidden("No Tienes Permiso Para Esta Acción");
      }
   }
}