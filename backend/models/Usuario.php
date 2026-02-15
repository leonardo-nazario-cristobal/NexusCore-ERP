<?php

require_once __DIR__ . '/../config/database.php';

class Usuario {
   private $db;

   public function __construct() {
      $this->db = Database::getConnection();
   }

   // Crear usuario
   public function create($nombre, $correo, $password, $rol = 'cajero') {
      $hash = password_hash($password, PASSWORD_DEFAULT);

      $sql = "INSERT INTO usuarios (nombre, correo, password, rol)
               VALUES (:nombre, :correo, :password, :rol)
               RETURNING id, nombre, correo, rol";

      $stmt = $this->db->prepare($sql);

      $stmt->execute([
         ':nombre' => $nombre,
         ':correo' => $correo,
         ':password' => $hash,
         ':rol' => $rol
      ]);

      return $stmt->fetch();
   }

   // Buscar por correo
   public function findByEmail($correo) {

      $sql = "
         SELECT *
         FROM usuarios
         WHERE correo = :correo
         LIMIT 1
      ";

      $stmt = $this->db->prepare($sql);

      $stmt->execute([
         ':correo' => $correo
      ]);

      return $stmt->fetch(PDO::FETCH_ASSOC);
   }

   // Buscar por ID
   public function findById($id) {
      $sql = "SELECT id, nombre, correo, rol, activo
               FROM usuarios
               WHERE id = :id";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);
      return $stmt->fetch();
   }

   // Verificar password
   public function verifyPassword($input, $hash) {
      return password_verify($input, $hash);
   }

   // Listar usuarios
   public function all() { 
      $sql = "SELECT id, nombre, correo, rol, activo, creado_en
               FROM usuarios
               ORDER BY id DESC";
      $stmt = $this->db->query($sql);
      return $stmt->fetchAll();
   }

   // Actualizar usuario
   public function update($id, $data) {
      $fields = [];
      $params = [':id' => $id];

      if  (isset($data['nombre'])) {
         $fields[] = "nombre = :nombre";
         $params[':nombre'] = $data['nombre'];
      }

      if (isset($data['correo'])) {
         $fields[] = "correo = :correo";
         $params[':correo'] = $data['correo'];
      }

      if (isset($data['rol'])) {
         $fields[] = "rol = :rol";
         $params[':rol'] = $data['rol'];
      }

      if (isset($data['activo'])) {
         $fields[] = "activo = :activo";
         $params[':activo'] = $data['activo'];
      }

      if (empty($fields)) {
         return false;
      }

      $sql = "UPDATE usuarios SET "
            . implode(', ', $fields)
            . " WHERE id = :id
               RETURNING id, nombre, correo, rol, activo";
      
      $stmt = $this->db->prepare($sql);
      $stmt->execute($params);

      return $stmt->fetch();
   }

   // Desactivar Usuario
   public function deactivate($id) {

      $sql = "UPDATE usuarios
            SET activo = false
            WHERE id = :id
            RETURNING id, nombre, correo, rol, activo";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);

      return $stmt->fetch();
   }
}