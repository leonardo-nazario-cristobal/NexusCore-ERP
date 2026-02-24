<?php
declare (strict_types=1);

/*

NexusCore ERP - API Entry Point

Controla:
- Headers
- CORS Seguro
- Manejo De Errores
- Carga De Configuración
- Enrutamiento API

*/

date_default_timezone_set('America/Mexico_City');

$isDev = php_sapi_name() === 'cli-server';

if ($isDev) {
   ini_set('display_errors', '1');
   ini_set('display_startup_errors', '1');
   error_reporting(E_ALL);
}

/* Headers Básicos */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

/* CORS Controldo */

header("Access-Control-Allow-Origin: *");
header("Access_Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

/* Preflight */

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
   http_response_code(204);
   exit;
}

/* Manejo Global De Errores */

set_error_handler(function ($serverity, $message, $file, $line) use ($isDev) {

   http_response_code(500);

   $response = [
      "status" => "error",
      "menssage" => "Error Interno Del Servidor"
   ];

   if ($isDev) {
      $response["debug"] = [
         "message" => $message,
         "file" => $file,
         "line" => $line
      ];
   }

   echo json_encode($response);
   exit;
});

/* Manejo Global De Excepciones */

set_exception_handler(function ($exception) use ($isDev) {

   http_response_code(500);

   $response = [
      "status" => "error",
      "message" => "Exception No Controlada"
   ];

   if ($isDev) {

      $response["debug"] = [
         "message" => $exception->getMessage(),
         "file" => $exception->getFile(),
         "line" => $exception->getLine()
      ];
   }

   echo json_encode($response);
   exit;
});

/*  Cargar Configuracion */

require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

/*  Utils */

require_once __DIR__ . '/utils/response.php';

/* Validar JSON En POST / PUT */

if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'], true)) {

   $input = file_get_contents("php://input");

   if ($input !== '' && $input !== null) {

      json_decode($input);

      if (json_last_error() !== JSON_ERROR_NONE) {
         http_response_code(400);
         echo json_encode([
            "status" => "error",
            "message" => "JSON inválido"
         ]);
         exit;
      }
   }
}

/* Rutas */

require_once __DIR__ . '/routes/api.php';