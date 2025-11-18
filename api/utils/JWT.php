<?php

namespace App\utils;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;

class JWT {
	private string $key;
	private string $algorithm = 'HS256';

	public function __construct() {
		$this->key = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production';
	}

	public function generateToken(array $payload): string {
		$payload['iat'] = time();
		$payload['exp'] = time() + 3600;
		return FirebaseJWT::encode($payload, $this->key, $this->algorithm);
	}

	public function validateToken(string $token): array|bool {
		try {
			$decoded = FirebaseJWT::decode($token, new Key($this->key, $this->algorithm));
			return (array) $decoded;
		} catch (SignatureInvalidException|BeforeValidException|ExpiredException) {
			return false;
		}
	}
}