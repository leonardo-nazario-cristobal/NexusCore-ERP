<?php 

declare (strict_types=1);

require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';
require_once __DIR__ . '/../utils/roleMiddleware.php';

class ProveedorController {

   private Proveedor $proveModel;

   public function __construct(PDO $connection) {
      $this->proveModel = new Proveedor($connection);
   }

   /* Crear */

   public function store(): void {
      
      RoleMiddleware::allow(['admin']);

      $input = $this->getJsonInput();

      if (empty(trim($input['nombre'] ?? '' ))) {
         Response::validationError(null, "Nombre Del Proveedor Es Obligatorio.");
      }

      $proveedor = $this->proveModel->create($input);

      Response::created($proveedor, "Proveedor Creado");

   }

   /* Listar */

   public function index(): void  {
      
      AuthMiddleware::verify();

      $data = $this->proveModel->all();

      Response::ok($data, "Lista De Proveedores.");
      
   }

   /* Buscar por ID */

   public function show(int $id): void {

      AuthMiddleware::verify();

      $proveedor = $this->proveModel->find($id);

      if (!$proveedor) {
         Response::notFound("Proveedor No encontrado.");
      }

      Response::ok($proveedor, "Detalle proveedor.");
      
   }

   /* Actualizar */

   public function update(int $id): void {
      
      RoleMiddleware::allow(['admin']);

      $input = $this->getJsonInput();

      if (empty(trim($input['nombre'] ?? '' ))) {
         Response::validationError(null, "Nombre Del Proveedor Es Obligatorio.");
      }

      $proveedor = $this->proveModel->update($id, $input);

      if (!$proveedor) {
         Response::notFound("Proveedor No Encontrado.");
      }

      Response::ok($proveedor, "Proveedor Actualizado.");

   }

   /* Eliminar */

   public function destroy(int $id): void {

      RoleMiddleware::allow(['admin']);

      $deleted = $this->proveModel->delete($id);

      if (!$deleted) {
         Response::notFound("Proveedor No Encontrado.");
      }

      Response::ok(null, "Proveedor Eliminado");

   }

   /* Utilidades */

   private function getJsonInput(): array {

      $input = json_decode(file_get_contents("php://input"), true);

      if (!is_array($input)) {
         Response::validationError(null, "JSON Invalido.");
      }

      return $input;

   }
}