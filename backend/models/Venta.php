<?php

declare (strict_types=1);

class Venta {

   private PDO $db;

   public function __construct(PDO $connection) {
      $this->db = $connection;
   }

   /* Crear Venta */

   public function create(array $data, int $userId): array {

      if (empty($data['detalles']) || !is_array($data['detalles'])) {
         throw new DateMalformedIntervalStringException("Venta Sin Detalle.");
      }

      try {

         $this->db->beginTransaction();

         $stmtVenta = $this->db->prepare(

            "INSERT INTO ventas (metodo_pago, id_usuario)
            VALUES (:metodo, :user)
            RETURNING id"

         );

         $stmtVenta->execute([
            ':metodo' => $data['metodo_pago'] ?? 'efectivo',
            ':user'   => $userId
         ]);

         $ventaId = (int) $stmtVenta->fetch(PDO::FETCH_ASSOC)['id'];

         $total = 0.0;

         $stmtProducto = $this->db->prepare(

            "SELECT precio, stock
            FROM productos
            WHERE id = :id
            FOR UPDATE

         ");

         $stmtDetalle = $this->db->prepare(

            "INSERT INTO detalle_venta
            (id_venta, id_producto, cantidad, precio_unitario, subtotal)
            VALUES (:v, :p, :cant, :precio, :sub)

         ");

         $stmtStock = $this->db->prepare(

            "UPDATE productos
            SET stock = stock - :cant
            WHERE id = :id

         ");

         $stmtMovimiento = $this->db->prepare(
            
            "INSERT INTO movimientos_inventario
            (id_producto, tipo, cantidad, motivo, id_usuario)
            VALUES (:p, 'salida', :cant, 'Venta', :u)
         ");

         foreach ($data['detalles'] as $d) {

            if (
               empty($d['id_producto']) ||
               empty($d['cantidad'])
            ) {
               throw new InvalidArgumentException("Detalle Invalido.");
            }

            $productoId = (int) $d['id_producto'];
            $cantidad   = (int) $d['cantidad'];

            if ($cantidad <= 0) {
               throw new InvalidArgumentException("Cantidad Invalida.");
            }

            /* Bloquear Producto */

            $stmtProducto->execute([':id' => $productoId]);
            $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
               throw new RuntimeException("Producto No Existe.");
            }

            $precio      = (float) $producto['precio'];
            $stockActual = (int) $producto['stock'];

            if ($stockActual < $cantidad) {
               throw new RuntimeException("Stock Insuficiente.");
            }

            $subtotal = $precio * $cantidad;
            $total += $subtotal;

            $stmtDetalle->execute([
               ':v'      => $ventaId,
               ':p'      => $productoId,
               ':cant'   => $cantidad,
               ':precio' => $precio,
               ':sub'    => $subtotal
            ]);

            /* Restar Stock */

            $stmtStock->execute([
               ':cant' => $cantidad,
               ':id'   => $productoId
            ]);

            if ($stmtStock->rowCount() === 0) {
               throw new RuntimeException("Error Al Actualizar Stock.");
            }

            /* Movimiento Inventario */

            $stmtMovimiento->execute([
               ':p'    => $productoId,
               ':cant' => $cantidad,
               ':u'    => $userId
            ]);
         }

         /* Actualizar Total */

         $stmtTotal = $this->db->prepare(

            "UPDATE ventas
            SET total = :t
            WHERE id = :id
         ");

         $stmtVenta->execute([
            ':t'  => $total,
            ':id' => $ventaId
         ]);

         $this->db->commit();

         return [
            'id'    => $ventaId,
            'total' => $total
         ];
         
      } catch (Throwable $e) {
         
         if ($this->db->inTransaction()) {
            $this->db->rollBack();
         }

         throw $e;
      }
   }

   /* Listar */

   public function all(): array {

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
      
      return $this->db
         ->query($sql)
         ->fetchAll(PDO::FETCH_ASSOC);

   }

   /* Buscar por ID */

   public function findById(int $id): ?array {

      $stmt = $this->db->prepare(
         "SELECT
            v.id,
            v.fecha,
            v.total,
            v.metodo_pago,
            u.nombre AS usuario
         FROM ventas v
         LEFT JOIN usuarios u
            ON v.id_usuario = u.id
         WHERE v.id = :id"
      );

      $stmt->execute([':id' => $id]);
      $venta = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$venta) {
         return null;
      }

      $det = $this->db->prepare(
         "SELECT
            dv.id_producto,
            p.nombre AS producto,
            dv.cantidad,
            dv.precio_unitario,
            dv.subtotal
         FROM detalle_venta dv
         INNER JOIN productos p
            ON dv.id_producto = p.id
         WHERE dv.id_venta = :id"
      );

      $det->execute([':id' => $id]);
      $venta['detalles'] = $det->fetchAll(PDO::FETCH_ASSOC);

      return $venta;
   }
}