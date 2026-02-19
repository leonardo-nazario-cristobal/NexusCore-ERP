<?php 

require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';

class VentaController {

   private $ventModel;

   public function __construct() {
      $this->ventModel = new Venta();
   }

   public function store() {
      $user = AuthMiddleware::verify();
      $input = json_decode(file_get_contents("php://input"), true);

      if (!$input || empty($input['detalles'])) {
         Response::badRequest("Venta Invalida");
      }

      try {
         $result = $this->ventModel->create($input, $user['sub']);

         Response::created($result, "Venta Registrada");
      } catch (Exception $e) {
         Response::serverError("Error Venta", $e->getMessage());
      }
   }

   public function index() {

      $user = AuthMiddleware::verify();
      $data = $this->ventModel->all();

      Response::ok($data, "Lista de Ventas");
   }

   public function show($id) {

      $user = AuthMiddleware::verify();

      $venta = $this->ventModel->find($id);

      if (!$venta) {
         Response::notFound($venta, "Venta no Encontrada");
      }

      Response::ok($venta, "Detalle de Venta");
   }
}