<?php

require_once __DIR__ . '/../config/database.php';

class Producto {

   private $db;

   public function __construct() {
      $this->db = Database::getConnection();
   }

   // Crear Producto
   public function create($data) {

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
               RETURNING id, id_categoria";

      $stmt = $this->db->prepare($sql);

      $stmt->execute([
         ':nombre' => $data['nombre'],
         ':descripcion' => $data['descripcion'] ?? null,
         ':precio' => $data['precio'],
         ':stock' => $data['stock'] ?? 0,
         ':stock_minimo' => $data['stock_minimo'] ?? 5,
         ':id_categoria' => $data['id_categoria'] ?? null
      ]);

      $producto = $stmt->fetch();

      $codigo = $this->generarCodigoBarras(
         $producto['id_categoria'],
         $producto['id']
      );

      $update = $this->db->prepare(
         "UPDATE productos
            SET codigo_barras = :codigo
            WHERE id = :id"
      );

      $update->execute([
         ':codigo' => $codigo,
         ':id' => $producto['id']
      ]);

      $final = $this->db->prepare(
         "SELECT id, codigo_barras, nombre, precio, stock, activo
            FROM productos
            WHERE id = :id"
      );

      $final->execute([':id' => $producto['id']]);

      return $final->fetch();
   }

   private function generarCodigoBarras($categoriaId, $productoId) {

      $cat = str_pad($categoriaId, 2, "0", STR_PAD_LEFT);

      $seq = str_pad($productoId, 8, "0", STR_PAD_LEFT);

      return $cat . "-" . $seq;
   }

   // Listar Productos
   public function all() {

      $sql = "SELECT id, codigo_barras, nombre, descripcion, precio, stock, stock_minimo, id_categoria, activo
            FROM productos
            ORDER BY id DESC";
      
      $stmt = $this->db->query($sql);
      return $stmt->fetchAll();
   }

   public function update($id, $data) {
      $fields = [];
      $params = [':id' => $id];
      
      if (isset($data['codigo_barras'])) {
         $fields[] = "codigo_barras = :codigo_barras";
         $params[':codigo_barras'] = $data['codigo_barras'];
      }

      if (isset($data['nombre'])) {
         $fields[] = "nombre = :nombre";
         $params[':nombre'] = $data['nombre'];
      }

      if (isset($data['descripcion'])) {
         $fields[] = "descripcion = :descripcion";
         $params[':descripcion'] = $data['descripcion'];
      }

      if (isset($data['precio'])) {
         $fields[] = "precio = :precio";
         $params[':precio'] = $data['precio'];
      }

      if (isset($data['stock'])) {
         $fields[] = "stock = :stock";
         $params[':stock'] = $data['stock'];
      }

      if (isset($data['stock_minimo'])) {
         $fields[] = "stock_minimo = :stock_minimo";
         $params[':stock_minimo'] = $data['stock_minimo'];
      }

      if (isset($data['activo'])) {
         $fields[] = "activo = :activo";
         $params[':activo'] = $data['activo'];
      }

      if (empty($fields)) {
         return false;
      }


      $sql = "UPDATE productos SET "
            . implode(', ', $fields)
            . " WHERE id = :id
               RETURNING id, codigo_barras, nombre, descripcion, precio, stock, stock_minimo, activo";
      
      $stmt = $this->db->prepare($sql);
      $stmt->execute($params);

      return $stmt->fetch();
   }

   // Desactivar Producto
   public function deactivate($id) {

      $sql = "UPDATE productos
            SET activo = false
            WHERE id = :id
            RETURNING id, codigo_barras, nombre, descripcion, precio, stock, stock_minimo, activo";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);

      return $stmt->fetch();
   }
}