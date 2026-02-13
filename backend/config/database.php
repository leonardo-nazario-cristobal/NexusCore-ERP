<?php

require_once __DIR__ . '/env.php';

class Database {
   public static function getConnection() {
      $host = getenv('DB_HOST');
      $db   = getenv('DB_NAME');
      $user = getenv('DB_USER');
      $pass = getenv('DB_PASS');
      $port = getenv('DB_PORT');

      if (!$host || !$db || !$user) {
         throw new Exception("Missing environment variables");
      }

      $dsn = "pgsql:host=$host;port=$port;dbname=$db;options='--client_encoding=UTF8'";

      try {
         $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
         ]);

         echo("conected");
         return $pdo;

      } catch (PDOException $e) {
         throw new Exception("Database connection failed");
      }
   }
}