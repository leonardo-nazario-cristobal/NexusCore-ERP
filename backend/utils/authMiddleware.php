<?php

declare (strict_types=1);

require_once __DIR__ . '/response.php';

class AuthMiddleware {

   private static function base64UrlDecode(string $data): string {

      return base64_decode(strtr($data, '-', '+/'));

   }

   public static function verify(): array {

      $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

      if (!$authHeader) {
         Response::unauthorized("Token Requerido.");
      }

      if(!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
         Response::unauthorized("Formato De Token Invalido.");
      }

      $token = $matches[1];

      $parts = explode('.', $token);

      if (count($parts) !== 3) {
         Response::unauthorized("Tokend Mal Formado.");
      }

      [$header, $payload, $signature] = $parts;

      $secret = getenv("JWT_SECRET");

      if (!$secret) {
         Response::error("JWT_SECRET No Configurado En El Servidor.", 500);
      }

      $validSignature = hash_hmac(
         'sha256',
         "$header.$payload",
         $secret,
         true
      );

      $validSignature = rtrim(
         strtr(base64_encode($validSignature), '+/', '-_'),
         '='
      );

      if (!hash_equals($validSignature, $signature)) {
         Response::unauthorized("Firma Invalida.");
      }

      $payloadData = json_decode(
         self::base64UrlDecode($payload),
         true
      );

      if (!is_array($payloadData)) {
         Response::unauthorized("Payload Invalido.");
      }

      if (!isset($payloadData['exp']) || $payloadData['exp'] < time()) {
         Response::unauthorized("Token Expirado.");
      }

      return $payloadData;
   }
}