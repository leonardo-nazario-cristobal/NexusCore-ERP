<?php

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$path = __DIR__ . $uri;

// Si exite el archivo fisico → servirlo
if ($uri !== "/" && file_exists($path)) {
   return false;
}

// API backend
if (strpos($uri, '/api') === 0) {
   require __DIR__ . '/backend/index.php';
   exit;
}

// Frontend por defecto
require __DIR__ . '/frontend/index.html';
