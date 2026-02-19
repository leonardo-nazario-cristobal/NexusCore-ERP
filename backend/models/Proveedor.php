<?php

require_once __DIR__ . '/../config/database.php';

class Proveedor {

   private $db;

   public function __construct() {
      $this->db = Database::getConnection();
   }

   public function create($data) {

      $stmt = $this->db->prepare(
         "INSERT INTO proveedores (nombre, telefono, correo)
         VALUES (:nombre, :telefono, :correo)
         RETURNING id");

      $stmt->execute([
         ':nombre' => $data['nombre'],
         ':telefono' => $data['telefono'],
         ':correo' => $data['correo']
      ]);

      return $stmt->fetch();
   }

   // Listar Proveedores
   public function all() {
      $stmt = $this->db->prepare(
         "SELECT id, nombre, telefono, correo
         FROM proveedores
         ORDER BY id DESC");

      $stmt->execute();
      return $stmt->fetchAll();
   }

   public function find($id) {
      $stmt = $this->db->prepare(
         "SELECT id, nombre, telefono, correo
         FROM proveedores
         WHERE id = :id");
      
      $stmt->execute([':id' => $id]);
      return $stmt->fetch();
   }
}