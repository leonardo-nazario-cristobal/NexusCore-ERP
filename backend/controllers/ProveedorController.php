<?php 

require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';

class ProveedorController {

   private $proveModel;

   public function __construct() {
      $this->proveModel = new Proveedor();
   }

   public function store() {
      $user = AuthMiddleware::verify();

      $input = json_decode(file_get_contents("php://input"), true);

      if (!$input || empty($input['nombre'])) {
         Response::badRequest("Nombre del Proveedor es Obligatorio");
      }

      try {
         $proveedor = $this->proveModel->create($input);

         Response::created($proveedor, "Proveedore Creado");

      } catch (Exception $e) {
         Response::serverError("Error Proveedor", $e->getMessage());
      }
   }

   public function index() {
      
      $user = AuthMiddleware::verify();

      $data = $this->proveModel->all();

      Response::ok($data, "Lista de Proveedores");
      
   }

   public function show($id) {

      AuthMiddleware::verify();

      $proveedor = $this->proveModel->find($id);

      if (!$proveedor) {
         Response::notFound("Proveedor no encontrado");
      }

      Response::ok($proveedor, "Detalle proveedor");
   }
}