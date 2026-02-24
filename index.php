<?php 

declare (strict_types=1);

/*

NexusCore ERP - Front Controller

Punto único de entrada del sistema.

Maneja:
- Archivos estáticos
- Rutas API
- Frontend SPA

*/

$basePath = realpath(__DIR__);
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/* Seguridad básica de headers */

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Option: nosniff');
header('Reffer-Policy: no-referrer-when-dowgrade');

/* Normalizar URI (evitar directory traversal) */

$cleanUri = str_replace(['..', '\\'], '', $requestUri);
$targetPath = realpath($basePath . $cleanUri);

/* Servir archivos estáticos si existen */

if (
   $cleanUri !== '/' &&
   $targetPath &&
   str_starts_with($targetPath, $basePath) &&
   is_file($targetPath)
) {
   return false;
}

/* API Backend */

if (str_starts_with($cleanUri, '/api')) {
   require $basePath . '/backend/index.php';
   exit;
}

/* Frontend (SPA fallback) */

$frontenFile = $basePath . '/frontend/index.html';

if (file_exists($frontendFile)) {
   require $frontendFile;
   exit;
}

/* Si nada coincide → 404 */

http_response_code(404);
echo json_encode([
   "error" => "Recurso No Encontrado"
]);
exit;