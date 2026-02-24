<?php

declare (strict_types=1);
class Usuario {

   private PDO $db;

   public function __construct(PDO $connection) {
      $this->db = $connection;
   }

   /* Crear usuario */

   public function create(
      string $nombre,
      string $correo,
      string $password,
      string $rol = 'cajero'
      ): array {
      
      $hash = password_hash($password, PASSWORD_DEFAULT);

      $sql = "INSERT INTO usuarios (nombre, correo, password, rol)
               VALUES (:nombre, :correo, :password, :rol)
               RETURNING id, nombre, correo, rol";

      $stmt = $this->db->prepare($sql);

      try {

         $stmt->execute([
            ':nombre'   => $nombre,
            ':correo'   => $correo,
            ':password' => $hash,
            ':rol'      => $rol
         ]);

         return $stmt->fetch();
      } catch (PDOException $e) {
         
         if ($e->getCode() === '23505') {
            throw new RangeException("Email Already Exists.");
         }

         throw $e;
      }
   }

   /* Buscar por correo */

   public function findByEmail(string $correo): ?array {

      $sql = "SELECT id, nombre, correo, password, rol, activo
         FROM usuarios
         WHERE correo = :correo
         LIMIT 1
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([':correo' => $correo]);
      
      $user = $stmt->fetch();

      return $user ?: null;
   }

   /* Buscar por ID */

   public function findById(int $id): ?array {

      $sql = "SELECT id, nombre, correo, rol, activo
               FROM usuarios
               WHERE id = :id
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);
      return $stmt->fetch() ?: null;
   }

   /* Verificar password */

   public function verifyPassword(string $input, string $hash): bool {

      return password_verify($input, $hash);

   }

   /* Listar usuarios */

   public function all(): array { 

      $sql = "SELECT id, nombre, correo, rol, activo, creado_en
               FROM usuarios
               ORDER BY id DESC
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }

   /* Actualizar usuario */
   
   public function update(int $id, array $data): ?array {

      $allowedFields = ['nombre', 'correo', 'rol', 'activo', 'password'];

      $fields = [];
      $params = [':id' => $id];

      foreach ($allowedFields as $field) {

         if (!array_key_exists($field, $data)) {
            continue;
         }

         if ($field === 'password') {
            $fields[] = "password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            continue;
         }

         $fields[] = "$field = :$field";
         $params[":$field"] = $data[$field];
      }

      if (empty($fields)) {
         throw new InvalidArgumentException("No Valid Fields Provided For Update");
      }

      $sql = "UPDATE usuarios
               SET " . implode(', ', $fields) . " 
               WHERE id = :id
               RETURNING id, nombre, correo, rol, activo
      ";

      $stmt = $this->db->prepare($sql);
      
      try {

         $stmt->execute($params);
         $updateUser = $stmt->fetch();

         return $updateUser ?: null;
      } catch (PDOException $e) {

         if ($e->getCode() === '23505') {
            throw new RuntimeException("Email Already Exists.");
         }

         throw $e;
      }
   }

   /* Desactivar Usuario */

   public function deactivate(int $id): ?array {

      $sql = "UPDATE usuarios
            SET activo = false
            WHERE id = :id
            RETURNING id, nombre, correo, rol, activo";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([':id' => $id]);

      return $stmt->fetch() ?: null;
   }
}