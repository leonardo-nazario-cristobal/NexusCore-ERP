<?php

declare (strict_types=1);

require_once __DIR__ . '/../models/MovimientoInventario.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';
require_once __DIR__ . '/../utils/roleMiddleware.php';

class MovimientoInventarioController {
   
   private MovimientoInventario $MovInModel;

   public function __construct(PDO $connection) {
      $this->MovInModel = new MovimientoInventario($connection);
   }

   /* Crear Movimiento */

   public function store(): void {

      $user = AuthMiddleware::verify();

      $input = json_decode(file_get_contents("php://input"), true);

      if (!is_array($input)) {
         Response::badRequest("JSON Invalido.");
         return;
      }

      if (
         empty($input['id_producto']) ||
         empty($input['tipo']) ||
         empty($input['cantidad'])
      ) {
         Response::validationError(null, "Datos, Incompletos.");
      }

      if ((int)$input['cantidad'] <= 0) {
         Response::validationError(null, "Cantidad Invalida");
      }

      if (!in_array($input['tipo'], ['entrada', 'salida', 'ajuste'], true)) {
         Response::validationError(null, "Tipo De Movimiento Invalido.");
      }

      $input['id_usuario'] = $user['sub'];

      try {

         $mov = $this->MovInModel->create($input);

         Response::created($mov, "Movimiento Generado.");

      } catch (InvalidArgumentException $e) {
         Response::validationError(null, $e->getMessage());
      } catch (Throwable $e) {
         Response::serverError("Error Al Generar Movimiento.");
      }
      
   }

   // Listar Movimientos 
   public function index() {

      AuthMiddleware::verify();

      try {

         $data = $this->MovInModel->all();
         Response::ok($data, "Historial Inventario.");
         
      } catch (Throwable $e) {
         Response::serverError("Error Al Listar Movimiento.", $e->getMessage());
      }
   }
}