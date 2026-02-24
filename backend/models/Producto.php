<?php

declare (strict_types=1);

class Producto {

   private PDO $db;

   public function __construct(PDO $connection) {
      $this->db = $connection;
   }

   /* Crear Producto */

   public function create(array $data): array {

      $sql = "INSERT INTO productos (
                  nombre,
                  descripcion,
                  precio,
                  stock,
                  stock_minimo,
                  id_categoria
               )
               VALUES (
                  :nombre,
                  :descripcion,
                  :precio,
                  :stock,
                  :stock_minimo,
                  :id_categoria
               )
               RETURNING id, id_categoria
      ";

      $stmt = $this->db->prepare($sql);

      $stmt->execute([
         ':nombre'       => trim($data['nombre']),
         ':descripcion'  => $data['descripcion'] ?? null,
         ':precio'       => $data['precio'],
         ':stock'        => $data['stock'] ?? 0,
         ':stock_minimo' => $data['stock_minimo'] ?? 5,
         ':id_categoria' => $data['id_categoria'] ?? null
      ]);

      $producto = $stmt->fetch(PDO::FETCH_ASSOC);

      $codigo = $this->generarCodigoBarras(
         (int)$producto['id_categoria'],
         (int)$producto['id']
      );

      $update = $this->db->prepare(
         "UPDATE productos
            SET codigo_barras = :codigo
            WHERE id = :id
      ");

      $update->execute([
         ':codigo' => $codigo,
         ':id'     => $producto['id']
      ]);

      return $this->find((int)$producto['id']);

   }

   /* Generar Codigo de Barras */

   private function generarCodigoBarras(
      
      int $categoriaId,
      int $productoId
      
   ): string {

      $cat = str_pad((string)$categoriaId, 2, "0", STR_PAD_LEFT);

      $seq = str_pad((string)$productoId, 8, "0", STR_PAD_LEFT);

      return $cat . "-" . $seq;

   }

   /* Listar Productos */

   public function all(): array {

      $sql = "SELECT 
                  p.id,
                  p.codigo_barras,
                  p.nombre,
                  p.precio,
                  p.stock,
                  p.activo,
                  c.nombre AS categoria
               FROM productos p
               LEFT JOIN categorias c
                  ON p.id_categoria = c.id
               WHERE p.activo = true
               ORDER BY p.id DESC
      ";
         
      return $this->db
         ->query($sql)
         ->fetchAll(PDO::FETCH_ASSOC);

   }

   /* Buscar por ID */

   public function find(int $id): ?array {

      $sql = "SELECT
                  p.id,
                  p.codigo_barras,
                  p.nombre,
                  p.descripcion,
                  p.precio,
                  p.stock,
                  p.stock_minimo,
                  p.activo,
                  c.nombre AS categoria
               FROM productos p
               LEFT JOIN categorias c
                  ON p.id_categoria = c.id
               WHERE p.id = :id
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);

      $result = $stmt->fetch(PDO::FETCH_ASSOC);


      return $result ?: null;

   }

   /* Actualizar */

   public function update(int $id, array $data): ?array {
      
      $fields = [];
      $params = [':id' => $id];

      $alloweb = [
         'codigo_barras',
         'nombre',
         'descripcion',
         'precio',
         'stock',
         'stock_minimo',
         'activo'
      ];

      foreach ($alloweb as $field) {

         if (isset($data[$field])) {

            $fields[] = "$field = :$field";
            $params[":$field"] = $data[$field];

         }
      }

      if (empty($field)) {
         return null;
      }

      $sql = "UPDATE productos SET "
            . implode(', ', $fields)
            . " WHERE id = :id
               RUTURNING id, codigo_barras, nombre, descripccion,
                        precio, stock, stock_minimo, activo
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute($params);

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result ?: null;

   }

   /* Desactivar Producto */

   public function deactivate(int $id): ?array {

      $sql = "UPDATE productos
               SET activo = false
               WHERE id = :id
               RETURNING id, codigo_barras, nombre,
                        descripcion, precio,
                        stock, stock_minimo, activo
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result ?: null;

   }
}