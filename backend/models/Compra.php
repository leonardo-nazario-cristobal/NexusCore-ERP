<?php

require_once __DIR__ .  '/../config/database.php';

class Compra {

   private $db;

   public function __construct() {
      $this->db = Database::getConnection();
   }

   public function create($data, $userId) {

      try {
         // Iniciar Transaccion
         $this->db->beginTransaction();

         $stmt = $this->db->prepare(
            "INSERT INTO compras (id_proveedor)
            VALUES (:prov)
            RETURNING id"
         );

         $stmt->execute([
            ':prov' => $data['id_proveedor']
         ]);

         $compraId = $stmt->fetch()['id'];

         $total = 0;

         if (empty($data['detalles'])) {
            throw new Exception("Compra sin detalles");
         }

         // Procesar Detalles
         foreach ($data['detalles'] as $d) {

            $subtotal = $d['cantidad'] * $d['costo_unitario'];
            $total += $subtotal;

            // Insertar Detalle
            $det = $this->db->prepare(
               "INSERT INTO detalle_compra
               (id_compra,id_producto,cantidad,costo_unitario)
               VALUES (:c,:p,:cant,:cost)");
            
            $det->execute([
               ':c'=>$compraId,
               ':p'=>$d['id_producto'],
               ':cant'=>$d['cantidad'],
               ':cost'=>$d['costo_unitario']
            ]);

            // Actualizar Stock
            $stock = $this->db->prepare(
               "UPDATE productos
               SET stock = stock + :cant
               WHERE id = :id");

            $stock->execute([
               ':cant'=>$d['cantidad'],
               ':id'=>$d['id_producto']
            ]);

            // Movimiento Inventario
            $mov = $this->db->prepare(
               "INSERT INTO movimientos_inventario
               (id_producto,tipo,cantidad,motivo,id_usuario)
               VALUES (:p,'entrada',:cant,'Compra',:u)");

            $mov->execute([
               ':p'=>$d['id_producto'],
               ':cant'=>$d['cantidad'],
               ':u'=>$userId
            ]);
         }

         // Actualizar Total Compra
         $upd = $this->db->prepare(
            "UPDATE compras
            SET total = :t
            WHERE id = :id"
         );

         $upd->execute([
            ':t'=>$total,
            ':id'=>$compraId
         ]);

         // Commit
         $this->db->commit();

         return [
            'id'=>$compraId,
            'total'=>$total
         ];
      } catch (Exception $e) {
         $this->db->rollBack();
         throw $e;
      }
   }

   public function all() {

      $sql = "SELECT
               c.id,
               c.fecha,
               c.total,
               p.nombre AS proveedor
            FROM compras c
            LEFT JOIN proveedores p
               ON c.id_proveedor = p.id
            ORDER BY c.id DESC";

      $stmt = $this->db->prepare($sql);
      $stmt->execute();

      return $stmt->fetchAll();
   }

}