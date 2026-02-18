<?php

require_once __DIR__ . '/../models/MovimientoInventario.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';
require_once __DIR__ . '/../utils/roleMiddleware.php';

class MovimientoInventarioController {
   
   private $MovInModel;

   public function __construct() {
      $this->MovInModel = new MovimientoInventario();
   }

   public function store() {

      $user = AuthMiddleware::verify();

      $input = json_decode(file_get_contents("php://input"), true);

      if (!$input) {
         Response::badRequest("JSON Invalido");
      }

      if (
         empty($input['id_producto']) ||
         empty($input['tipo']) ||
         empty($input['cantidad'])
      ) {
         Response::validationError(null, "Datos, Incompletos");
      }

      if ($input['cantidad'] <= 0) {
         Response::validationError(null, "Cantidad Invalida");
      }

      $input['id_usuario'] = $user['sub'];

      try {
         $mov = $this->MovInModel->create($input);

         Response::created($mov, "Movimiento Generado");
      } catch (PDOException $e) {
         Response::serverError("Error al Genear Movimiento", $e->getMessage());
      }
      
   }

   // Listar Movimientos 
   public function index() {

      $user = AuthMiddleware::verify();

      $data = $this->MovInModel->all();

      Response::ok($data, "Historial Inventario");
   }
}