<?php

declare (strict_types=1);

class MovimientoInventario {
   
   private PDO $db;

   public function __construct(PDO $connection) {
      $this->db = $connection;
   }

   /* Registrar movimientos */

   public function create(array $data): array {

      if (
         empty($data['id_producto']) ||
         empty($data['tipo']) ||
         !isset($data['cantidad']) ||
         empty($data['id_usuario'])
      ) {
         throw new InvalidArgumentException("Datos De Movimiento Incompletos.");
      }

      $productoId = (int) $data['id_producto'];
      $tipo       = (string) $data['tipo'];
      $cantidad   = (int) $data['cantidad'];
      $usuarioId  = (int) $data['id_usuario'];
      $motivo     = $data['motivo'] ?? null;

      if ($productoId <= 0 || $usuarioId <= 0) {
         throw new InvalidArgumentException("ID Invalido.");
      }

      if ($cantidad <= 0) {
         throw new InvalidArgumentException("Cantidad Invalida.");
      }

      if (!in_array($tipo, ['entrada', 'salida', 'ajuste'], true)) {
         throw new InvalidArgumentException("Tipo De Movimiento Invalido.");
      }

      try {

         $this->db->beginTransaction();

         /* Insertar Movimiento */

         $stmt = $this->db->prepare(

            "INSERT INTO movimientos_inventario
            (id_producto, tipo, cantidad, motivo, id_usuario)
            VALUES (:id_producto, :tipo, :cantidad, :motivo, :id_usuario)
            RETURNING id, id_producto, tipo, cantidad, motivo, fecha

         ");

         $stmt->execute([
            ':id_producto' => $productoId,
            ':tipo'        => $tipo,
            ':cantidad'    => $cantidad,
            ':motivo'      => $motivo,
            ':id_usuario'  => $usuarioId
         ]);

         $movimiento = $stmt->fetch(PDO::FETCH_ASSOC);

         /* Actualizar Stock */

         $this->actualizarStock($productoId, $tipo, $cantidad);

         $this->db->commit();

         return $movimiento;

      } catch (Throwable $e) {

         if ($this->db->inTransaction()) {
            $this->db->rollBack();
         }

         throw $e;
      }
   }

   /* Listar Historial */

   public function all(): array {

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
               ORDER BY m.fecha DESC
      ";
      
      return $this->db
         ->query($sql)
         ->fetchAll(PDO::FETCH_ASSOC);
   }

   /* Actualizar Stock */

   private function actualizarStock(

      int $productoId,
      string $tipo,
      int $cantidad

      ): void {

      if ($tipo === 'entrada') {

         $sql = "UPDATE productos
                  SET stock = stock + :cantidad
                  WHERE id = :id
         ";

      } elseif ($tipo === 'salida') {
      
         $sql = "UPDATE productos
                  SET stock = stock - :cantidad
                  WHERE id = :id
         ";

      } else {

         $sql = "UPDATE productos
                  SET stock = :cantidad
                  WHERE id = :id
         ";

      }

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
         ':cantidad' => $cantidad,
         ':id'       => $productoId
      ]);

      if ($stmt->rowCount() === 0) {
         throw new RuntimeException("Producto No Encontrado.");
      }
   }
}