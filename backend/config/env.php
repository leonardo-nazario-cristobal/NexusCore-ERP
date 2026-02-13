<?php

function loadEnv($path) {
   if (!file_exists($path)) {
		throw new Exception(".env file not found at $path");
	}

	$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

	foreach ($lines as $line) {

		// Inorar comentarios
		if (str_starts_with(trim($line), '#')) {
			continue;
		}

		list($name, $value) = explode('=', $line, 2);
		$name  = trim($name);
		$value = trim($value);

		// Guardar variables
		$_ENV[$name] = $value;
		putenv("$name=$value");

	}
}

// Cargar .env desde la raiz del proyecto
loadEnv(__DIR__ . '/../../.env');