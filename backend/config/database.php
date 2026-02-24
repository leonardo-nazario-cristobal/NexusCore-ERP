<?php

declare (strict_types=1);
class Database {

   public static function getConnection(): PDO {
      $host = getenv('DB_HOST');
      $db   = getenv('DB_NAME');
      $user = getenv('DB_USER');
      $pass = getenv('DB_PASS');
      $port = getenv('DB_PORT');

      if (!$host || !$db || !$user) {
         throw new RuntimeException("Database Envirinment Variables Are Missing.");
      }

      $dsn = sprintf(
         "pgsql:host=%s;port=%s;dbname=%s;options='--client_encoding=UTF8'",
         $host,
         $port,
         $db
      );

      try {
         return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
         ]);

      } catch (PDOException $e) {
         throw new RuntimeException("Database connection failed.");
      }
   }
}