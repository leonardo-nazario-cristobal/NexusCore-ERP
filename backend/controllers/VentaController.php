<?php 

declare (strict_types=1);

require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';

class VentaController {

   private Venta $ventModel;

   public function __construct(PDO $connection) {
      $this->ventModel = new Venta($connection);
   }

   /* Crear Venta */

   public function store(): void {
      
      $user = AuthMiddleware::verify();

      $input = json_decode(file_get_contents("php://input"), true);

      if (!is_array($input)) {
         Response::badRequest("JSON Invalido.");
         return;
      }

      if (empty($input['detalles'])) {
         Response::badRequest("Venta Invalida.");
         return;
      }

      try {

         $result = $this->ventModel->create(
            $input, 
            (int) $user['sub']
         );

         Response::created($result, "Venta Registrada.");

      } catch (InvalidArgumentException $e) {
         Response::badRequest($e->getMessage());
      } catch (Throwable $e) {
         Response::serverError("Error En Venta.", $e->getMessage());
      }
   }

   /* Listar Venta */

   public function index() {

      AuthMiddleware::verify();

      try {

         $data = $this->ventModel->all();
         Response::ok($data, "Lista De Ventas.");
      } catch (Throwable $e) {
         Response::serverError("Error Al Listar Ventas.", $e->getMessage());
      }
   }

   public function show(int $id): void {

      AuthMiddleware::verify();

      if ($id <= 0) {
         Response::badRequest("ID Invalido.");
         return;
      }

      try {

         $venta = $this->ventModel->findById($id);

         if (!$venta) {
            Response::notFound("Venta No Encontrada.");
            return;
         }

         Response::ok($venta, "Detalle De Venta.");
      } catch (Throwable $e) {
         Response::serverError("Error Al Obtener Venta.", $e->getMessage());
      }
   }
}