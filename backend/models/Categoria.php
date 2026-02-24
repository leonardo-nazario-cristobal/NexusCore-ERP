<?php

declare (strict_types=1);

class Categoria {

   private PDO $db;

   public function __construct(PDO $connection) {
      $this->db =  $connection;
   }

   /* Crear */

   public function create(array $data): array {

      $sql = "INSERT INTO categorias (nombre, descripcion)
               VALUES (:nombre, :descripcion)
               RETURNING id, nombre, descripcion
      ";
      
      $stmt = $this->db->prepare($sql);
      $stmt->execute([
         ':nombre'      => trim($data['nombre']),
         ':descripcion' => $data['descripcion'] ?? null
      ]);

      return $stmt->fetch(PDO::FETCH_ASSOC);
   }

   /* Listar */

   public function all(): array {

      $sql = "SELECT id, nombre, descripcion
               FROM categorias
               ORDER BY id DESC
      ";
      
      return $this->db
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
   }

   /* Buscar por ID */

   public function find(int $id): ?array {

      $sql = "SELECT id, nombre, descripcion
               FROM categorias
               WHERE id = :id
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result ?: null;
   }

   /* Actualizar */

   public function update(int $id, array $data): ?array {
      
      $sql = "UPDATE categorias
               SET nombre = :nombre,
                  descripcion = :descripcion
               WHERE id = :id
               RETURNING id, nombre, descripcion
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
         ':id'          => $id,
         ':nombre'      => $data['nombre'],
         ':descripcion' => $data['descripcion'] ?? null
      ]);

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result ?: null;
   }

   /* Eliminar */

   public function delete(int $id): bool {

      $sql = "DELETE FROM categorias WHERE id = :id";
      
      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);

      return $stmt->rowCount() < 0;
   }
}