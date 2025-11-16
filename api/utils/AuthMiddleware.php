<?php

namespace App\utils;

use JsonException;

class AuthMiddleware {
	public static function authenticate(): array {
		$headers = getallheaders();
		$auth_header = $headers['Authorization'] ?? null;

		if (!$auth_header || !str_starts_with($auth_header, 'Bearer ')) {
			http_response_code(401);
			try {
				echo json_encode(['error' => 'Authorization header required'], JSON_THROW_ON_ERROR);
			} catch (JsonException) {
				echo '{"error": "JSON encoding error"}';
			}
			exit();
		}

		$token = substr($auth_header, 7);
		$jwt = new JWT();
		$decoded = $jwt->validateToken($token);

		if (!$decoded) {
			http_response_code(401);
			try {
				echo json_encode(['error' => 'Invalid token'], JSON_THROW_ON_ERROR);
			} catch (JsonException) {
				echo '{"error": "JSON encoding error"}';
			}
			exit();
		}

		return $decoded;
	}

	public static function requireRole(string $required_role): array {
		$user = self::authenticate();
		if ($user['role'] !== $required_role && $user['role'] !== 'admin') {
			http_response_code(403);
			try {
				echo json_encode(['error' => 'Insufficient permissions'], JSON_THROW_ON_ERROR);
			} catch (JsonException) {
				echo '{"error": "JSON encoding error"}';
			}
			exit();
		}
		return $user;
	}
}