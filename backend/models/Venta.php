<?php

require_once __DIR__ . '/../config/database.php';

class Venta {

   private $db;

   public function __construct() {
      $this->db = Database::getConnection();
   }

   public function create($data, $userId) {
      try {

         $this->db->beginTransaction();

         // Crear Venta
         $stmt = $this->db->prepare(
            "INSERT INTO ventas (metodo_pago, id_usuario)
            VALUES (:metodo, :user)
            RETURNING id");

         $stmt->execute([
            ':metodo' => $data['metodo_pago'] ?? 'efectivo',
            ':user' => $userId
         ]);

         $ventaId = $stmt->fetch()['id'];
         $total = 0;

         foreach ($data['detalles'] as $d) {

            // Obtener producto
            $prod = $this->db->prepare(
               "SELECT precio, stock
               FROM productos
               WHERE id = :id
               FOR UPDATE");

            $prod->execute([':id' => $d['id_producto']]);
            $producto = $prod->fetch();

            if (!$producto) {
               throw new Exception("Producto no Existe");
            }

            if ($producto['stock'] < $d['cantidad']) {
               throw new Exception("Stock Insuficiente");
            }

            $precio = $producto['precio'];
            $subtotal = $precio * $d['cantidad'];
            $total += $subtotal;

            // Insertar Detalle
            $det = $this->db->prepare(
               "INSERT INTO detalle_venta
               (id_venta,id_producto,cantidad,precio_unitario,subtotal)
               VALUES (:v,:p,:cant,:precio,:sub)");

            $det->execute([
               ':v'=>$ventaId,
               ':p'=>$d['id_producto'],
               ':cant'=>$d['cantidad'],
               ':precio'=>$precio,
               ':sub'=>$subtotal
            ]);

            // Restar Stock
            $stock = $this->db->prepare(
               "UPDATE productos
               SET stock = stock - :cant
               WHERE id = :id");
            
            $stock->execute([
               ':cant'=>$d['cantidad'],
               ':id'=>$d['id_producto']
            ]);

            // Movimiento Salida
            $mov = $this->db->prepare(
               "INSERT INTO movimientos_inventario
               (id_producto,tipo,cantidad,motivo,id_usuario)
               VALUES (:p,'salida',:cant,'Venta',:u)");

            $mov->execute([
               ':p'=>$d['id_producto'],
               ':cant'=>$d['cantidad'],
               ':u'=>$userId
            ]);
         }

         // Actualizar Total
         $upd = $this->db->prepare(
            "UPDATE ventas
            SET total = :t
            WHERE id = :id");
         
         $upd->execute([
            ':t'=>$total,
            ':id'=>$ventaId
         ]);

         $this->db->commit();

         return [
            'id'=>$ventaId,
            'total'=>$total
         ];
      } catch (Exception $e) {
         
         $this->db->rollBack();
         throw $e;
      }
   }

   public function all() {

      $sql = "SELECT
                  v.id,
                  v.fecha,
                  v.total,
                  v.metodo_pago,
                  u.nombre AS usuario
               FROM ventas v
               LEFT JOIN usuarios u
                  ON v.id_usuario = u.id
               ORDER BY v.id DESC";
      
      $stmt = $this->db->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll();
   }

   public function find($id) {

      // Cabecera
      $stmt = $this->db->prepare(
         "SELECT
            v.id,
            v.fecha,
            v.total,
            v.metodo_pago,
            u.nombre AS usuario
         FROM ventas v
         LEFT JOIN usuarios u ON v.id_usuario = u.id
         WHERE v.id = :id");

      $stmt->execute([':id' => $id]);
      $venta = $stmt->fetch();

      if (!$venta) {
         return null;
      }

      // Detalles
      $det = $this->db->prepare(
         "SELECT
            dv.id_producto,
            p.nombre AS producto,
            dv.cantidad,
            dv.precio_unitario,
            dv.subtotal
         FROM detalle_venta dv
         JOIN productos p ON dv.id_producto = p.id
         WHERE dv.id_venta = :id");

      $det->execute([':id' => $id]);
      $venta['detalles'] = $det->fetchAll();

      return $venta;
   }
}