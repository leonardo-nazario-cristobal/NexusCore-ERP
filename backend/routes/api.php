<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Quitar prefijo /backend si existe
$uri = str_replace('/backend', '', $uri);

switch ($uri) {

   case '/api/test':
      if ($method === 'GET') {
         Response::success([
            "msg" => "Router funcionando 🚀"
         ]);
      }
      break;

   default:
      Response::error("Endpoint no encontrado", 404);
}
