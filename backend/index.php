<?php

// Headers API
header("Content-Type: application/json");

// CORS (para que el frontend pueda llamar la API)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
   exit(0);
}

// Configuracion
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

// Utils 
require_once __DIR__ . '/utils/response.php';

// Rutas
require_once __DIR__ . '/routes/api.php';
