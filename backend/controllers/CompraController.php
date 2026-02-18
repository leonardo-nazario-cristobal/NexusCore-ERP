<?php

require_once __DIR__ . '/../models/Compra.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';

class CompraController {

   private $CompModel;

   public function __construct() {
      $this->CompModel = new Compra();
   }

   public function store() {

      $user = AuthMiddleware::verify();

      $input = json_decode(file_get_contents("php://input"), true);

      if (!$input || empty($input['detalles'])) {
         Response::badRequest("Compra Invalida");
      }

      try {
         $result = $this->CompModel->create($input, $user['sub']);

         Response::created($result, "Compra Registrada");
      } catch (Exception $e) {
         Response::serverError("Error Compra", $e->getMessage());
      }
   }

   public function index() {

      AuthMiddleware::verify();

      $data = $this->CompModel->all();

      Response::ok($data, "Lista de Compras");
   }

}