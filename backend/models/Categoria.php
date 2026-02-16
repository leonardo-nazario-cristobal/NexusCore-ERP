<?php

require_once __DIR__ . '/../config/database.php';

class Categoria {

   private $db;

   public function __construct() {
      $this->db = Database::getConnection();
   }

   // Crear
   public function create($data) {

      $sql = "INSERT INTO categorias (nombre, descripcion)
               VALUES (:nombre, :descripcion)
               RETURNING id, nombre, descripcion";
      
      $stmt = $this->db->prepare($sql);
      $stmt->execute([
         ':nombre' => $data['nombre'],
         ':descripcion' => $data['descripcion'] ?? null
      ]);

      return $stmt->fetch();
   }

   // Listar
   public function all() {

      $sql = "SELECT id, nombre, descripcion
               FROM categorias
               ORDER BY id DESC";
      
      return $this->db->query($sql)->fetchAll();
   }

   // Mostrar una por ID
   public function find($id) {

      $sql = "SELECT id, nombre, descripcion
               FROM categorias
               WHERE id = :id";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);

      return $stmt->fetch();
   }

   // Actualizar
   public function update($id, $data) {
      
      $sql = "UPDATE categorias
               SET nombre = :nombre,
                  descripcion = :descripcion
               WHERE id = :id
               RETURNING id, nombre, descripcion";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([
         ':id' => $id,
         ':nombre' => $data['nombre'],
         ':descripcion' => $data['descripcion'] ?? null
      ]);

      return $stmt->fetch();
   }

   // Eliminar
   public function delete($id) {

      $sql = "DELETE FROM categorias
               WHERE id = :id";
      
      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);

      return $stmt->rowCount();
   }
}