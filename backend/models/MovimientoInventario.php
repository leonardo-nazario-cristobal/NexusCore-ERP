<?php

require_once __DIR__ . '/../config/database.php';

class MovimientoInventario {
   
   private $db;

   public function __construct() {
      $this->db = Database::getConnection();
   }

   // Registrar movimientos
   public function create($data) {

      $sql = "INSERT INTO movimientos_inventario
               (id_producto, tipo, cantidad, motivo, id_usuario)
               VALUES
               (:id_producto, :tipo, :cantidad, :motivo, :id_usuario)
               RETURNING id, id_producto, tipo, cantidad, motivo, fecha";
      $stmt = $this->db->prepare($sql);

      $stmt->execute([
         ':id_producto' => $data['id_producto'],
         ':tipo' => $data['tipo'],
         ':cantidad' => $data['cantidad'],
         ':motivo' => $data['motivo'] ?? null,
         ':id_usuario' => $data['id_usuario']
      ]);

      $this->actualizarStock(
         $data['id_producto'],
         $data['tipo'],
         $data['cantidad']
      );

      return $stmt->fetch();
   }

   // Listar Historial
   public function all() {

      $sql = "SELECT
                  m.id,
                  p.nombre AS producto,
                  m.tipo,
                  m.cantidad,
                  m.motivo,
                  u.nombre AS usuario,
                  m.fecha
               FROM movimientos_inventario m
               LEFT JOIN productos p ON m.id_producto = p.id
               LEFT JOIN usuarios u ON m.id_usuario = u.id
               ORDER BY m.fecha DESC";
      
      $stmt = $this->db->prepare($sql);
      $stmt->execute();

      return $stmt->fetchAll();
   }

   private function actualizarStock($productoId, $tipo, $cantidad) {

      if ($tipo === 'entrada') {
         $sql = "UPDATE productos
                  SET stock = stock + :cantidad
                  WHERE id = :id";
      }

      elseif ($tipo === 'salida') {
         $sql = "UPDATE productos
                  SET stock = stock - :cantidad
                  WHERE id = :id";
      }

      else {
         $sql = "UPDATE productos
                  SET stock = :cantidad
                  WHERE id = :id";
      }

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
         ':cantidad' => $cantidad,
         ':id' => $productoId
      ]);
   }
}