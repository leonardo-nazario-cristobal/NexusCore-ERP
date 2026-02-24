<?php

declare (strict_types=1);

class Compra {

   private PDO $db;

   public function __construct(PDO $connection) {
      $this->db = $connection;
   }

   /* Crear Compra */

   public function create(array $data, int $userId): array {

      if (empty($data['id_proveedor'])) {
         throw new InvalidArgumentException("Proveedor Requerido.");
      }

      if (empty($data['detalles']) || !is_array($data['detalles'])) {
         throw new InvalidArgumentException("Compra Sin Detalles.");
      }

      try {

         $this->db->beginTransaction();

         /* Crear Compra */

         $stmt = $this->db->prepare(
            "INSERT INTO compras (id_proveedor)
            VALUES (:prov)
            RETURNING id"
         );

         $stmt->execute([':prov' => $data['id_proveedor']]);

         $compraId = (int) $stmt->fetch(PDO::FETCH_ASSOC)['id'];

         $total = 0;

         // Procesar Detalles
         foreach ($data['detalles'] as $d) {

            if (
               empty($d['id_producto']) ||
               empty($d['cantidad']) ||
               empty($d['costo_unitario'])
            ) {

               throw new InvalidArgumentException("Detalle Invalido.");

            }

            $cantidad = (int) $d['cantidad'];
            $costo    = (float) $d['costo_unitario'];

            if ($cantidad <= 0 || $costo < 0) {
               throw new InvalidArgumentException("Cantidad O Costo Invalido.");
            }

            $subtotal = $cantidad * $costo;
            $total += $subtotal;

            /* Insertar Detalle */

            $det = $this->db->prepare(
               "INSERT INTO detalle_compra
               (id_compra, id_producto, cantidad, costo_unitario)
               VALUES (:c, :p, :cant, :cost)
            ");
            
            $det->execute([
               ':c'    => $compraId,
               ':p'    => $d['id_producto'],
               ':cant' => $cantidad,
               ':cost' => $costo
            ]);

            /* Actualizar Stock */

            $stock = $this->db->prepare(
               "UPDATE productos
               SET stock = stock + :cant
               WHERE id = :id
            ");

            $stock->execute([
               ':cant' => $cantidad,
               ':id'   => $d['id_producto']
            ]);

            if ($stock->rowCount() === 0) {
               throw new RuntimeException("Producto No Encontrado.");
            }

            /* Movimiento Inventario */

            $mov = $this->db->prepare(
               "INSERT INTO movimientos_inventario
               (id_producto, tipo, cantidad, motivo, id_usuario)
               VALUES (:p, 'entrada', :cant, 'Compra, :u)
            ");

            $mov->execute([
               ':p'    => $d['id_producto'],
               ':cant' => $cantidad,
               ':u'    => $userId
            ]);
         }

         /* Actualizar Total Compra */

         $upd = $this->db->prepare(
            "UPDATE compras
            SET total = :t
            WHERE id = :id
         ");

         $upd->execute([
            ':t'  => $total,
            ':id' => $compraId
         ]);

         $this->db->commit();

         return [
            'id'    => $compraId,
            'total' => $total
         ];

      } catch (Throwable $e) {
         
         if ($this->db->inTransaction()) {
            $this->db->rollBack();
         }

         throw $e;

      }
   }

   /* Listar Compras */

   public function all(): array {

      $sql = "SELECT
               c.id,
               c.fecha,
               c.total,
               p.nombre AS proveedor
            FROM compras c
            LEFT JOIN proveedores p
               ON c.id_proveedor = p.id
            ORDER BY c.id DESC";

      return $this->db
         ->query($sql)
         ->fetchAll(PDO::FETCH_ASSOC);
      
   }

   /* Buscar por ID */

   public function findById(int $id): ?array {

      $sqlCompra = "SELECT
               c.id,
               c.fecha,
               c.total,
               p.nombre AS proveedor
            FROM compras c
            LEFT JOIN proveedores p
               ON c.id_proveedor = p.id
            WHERE c.id = :id
      ";

      $stmt = $this->db->prepare($sqlCompra);
      $stmt->execute([':id' => $id]);

      $compra = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$compra) {
         return null;
      }

      $sqlDetalle = "SELECT 
                        d.id_producto,
                        pr.nombre AS producto,
                        d.cantidad,
                        d.costo_unitario
                     FROM detalle_compra d
                     INNER JOIN productos pr
                        ON d.id_producto = pr.id
                     WHERE d.id_compra = :id
      ";

      $stmtDetalle = $this->db->prepare($sqlDetalle);
      $stmtDetalle->execute([':id' => $id]);

      $detalles = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

      $compra['detalles'] = $detalles;

      return $compra;

   }
}