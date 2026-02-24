<?php

declare (strict_types=1);

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/authMiddleware.php';

class RoleMiddleware {

   public static function allow(array $rolesPermitidos): array {

      $user = AuthMiddleware::verify();

      if (!isset($user['rol']) ||
         !in_array($user['rol'], $rolesPermitidos, true)) {
         
         Response::forbidden("No Tienes Permiso Para Realizar Esta Acción.");
      }

      return $user;
   }

   public static function isAdmin(array $user): bool {

      $user = AuthMiddleware::verify();

      return isset($user['rol']) && $user['rol'] === 'admin';

   }
}