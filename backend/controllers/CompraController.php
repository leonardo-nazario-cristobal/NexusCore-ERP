<?php

declare (strict_types=1);

require_once __DIR__ . '/../models/Compra.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/authMiddleware.php';

class CompraController {

   private Compra $compModel;

   public function __construct(PDO $connection) {
      $this->compModel = new Compra($connection);
   }

   /* Crear Compra */

   public function store(): void {

      $user = AuthMiddleware::verify();

      $input = json_decode(file_get_contents("php://input"), true);

      if (!is_array($input)) {
         Response::badRequest("JSON Invalido.");
      }

      if (empty($input['detalles'])) {
         Response::badRequest("Compra Invalida.");
      }

      try {

         $result = $this->compModel->create(
            $input,
            $user['sub']
         );

         Response::created($result, "Compra Registrada.");

      } catch (InvalidArgumentException $e) {

         Response::badRequest($e->getMessage());

      } catch (Throwable $e) {

         Response::serverError("Error En Compra.", $e->getMessage());
      }
   }

   /* Listar Compras */

   public function index(): void {

      AuthMiddleware::verify();

      try {

         $data = $this->compModel->all();
         Response::ok($data, "Lista De Compras.");

      } catch (Throwable $e) {

         Response::serverError("Error Al Listar.", $e->getMessage());
         
      }
   }

   /* Bucar por ID */

   public function show(int $id): void {

      AuthMiddleware::verify();

      if ($id <= 0) {
         Response::badRequest("ID inválido.");
         return;
      }

      try {

         $compra = $this->compModel->findById($id);

         if (!$compra) {
            Response::notFound("Compra no encontrada.");
            return;
         }

         Response::ok($compra);

      } catch (Throwable $e) {

         Response::serverError("Error al obtener compra.", $e->getMessage());
      }
   }
}