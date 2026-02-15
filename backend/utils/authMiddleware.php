<?php

require_once __DIR__ . '/response.php';

class AuthMiddleware {
   private static function base64UrlDecode($data) {
      return base64_decode(strtr($data, '-', '+/'));
   }

   public static function verify() {
      $headers = getallheaders();

      if (!isset($headers['Authorization'])) {
         Response::unauthorized("Token Requerido");
      }

      $authHeader = $headers['Authorization'];

      if(!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
         Response::unauthorized("Formato de Token Invalido");
      }

      $token = $matches[1];

      $parts = explode('.', $token);

      if (count($parts) !== 3) {
         Response::unauthorized("Tokend mal Formado");
      }

      [$header, $payload, $signature] = $parts;

      $secret = getenv("JWT_SECRET");

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
         Response::unauthorized("Firma Invalida");
      }

      $payloadData = json_decode(
         self::base64UrlDecode($payload),
         true
      );

      if ($payloadData['exp'] < time()) {
         Response::unauthorized("Token Expirado");
      }

      return $payloadData;
   }
}