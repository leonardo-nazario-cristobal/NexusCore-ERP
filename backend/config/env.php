<?php

function loadEnv(string $path): void {

   if (!file_exists($path)) {
		throw new RuntimeException(".env file not found at $path");
	}

	$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

	foreach ($lines as $line) {

		$line = trim($line);

		/* Inorar Comentarios Y Lineas Vacias */

		if ($line === '' || str_starts_with($line, '#')) {
			continue;
		}

		/* Asegurar Que Tenga '=' */

		if (!str_contains($line, '=')) {
			continue;
		}

		[$name, $value] = explode('=', $line, 2);

		$name = trim($name);
		$value = trim($value);

		/* Quitar Comillas Si Existen */

		if (
			(str_starts_with($value, '"') && str_ends_with($value, '"')) ||
			(str_starts_with($value, "'") && str_ends_with($value, "'"))
		) {
			$value = trim($value, 1, -1);
		}

		/* No Sobrescribir Si Ya Existe */

		if (!array_key_exists($name, $_ENV)) {
			$_ENV[$name] = $value;
			putenv("$name=$value");
		}
	}
}

/* Cargar .env desde la raiz del proyecto */

loadEnv(dirname(__DIR__, 2). '/.env');