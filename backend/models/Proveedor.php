<?php

declare (strict_types=1);

class Proveedor {

   private PDO $db;

   public function __construct(PDO $connection) {
      $this->db = $connection;
   }

   /* Crear */

   public function create(array $data): array {

      $sql = "INSERT INTO proveedores (nombre, telefono, correo)
               VALUES (:nombre, :telefono, :correo)
               RETURNING id, nombre, telefono, correo
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
         ':nombre'   => trim($data['nombre']),
         ':telefono' => $data['telefono'] ?? null,
         ':correo'   => $data['correo'] ?? null
      ]);

      return $stmt->fetch(PDO::FETCH_ASSOC);

   }

   /* Listar */

   public function all(): array {
      
      $sql = "SELECT id, nombre, telefono, correo
               FROM proveedores
               ORDER BY id DESC
      ";

      return $this->db
         ->query($sql)
         ->fetchAll(PDO::FETCH_ASSOC);

   }

   /* Buscar por ID */

   public function find(int $id): ?array {

      $sql = "SELECT id, nombre, telefono, correo
               FROM proveedores
               WHERE id = :id
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result ?: null;

   }

   /* Actualizar */

   public function update(int $id, array $data): ?array {

      $sql = "UPDATE proveedores
               SET nombre = :nombre,
                  telefono = :telefono,
                  correo = :correo
               WHERE id = :id
               RETURNING id, nombre, telefono, correo
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
         ':id'       => $id,
         ':nombre'   => trim($data['nombre']),
         ':telefono' => $data['telefono'] ?? null,
         ':correo'   => $data['correo'] ?? null
      ]);

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result ?: null;

   }

   /* Eliminar */

   public function delete(int $id): bool {

      $sql = "DELETE FROM proveedores WHERE id = :id";
      
      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);

      return $stmt->rowCount() > 0;
   }
}