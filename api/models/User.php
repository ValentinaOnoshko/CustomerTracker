<?php

namespace App\models;

use App\utils\JWT;
use PDO;

class User extends Model {
	protected string $table_name = 'users';

	public function create(array $data): bool {
		$query = "INSERT INTO " . $this->table_name . " (username, password, role) VALUES (:username, :password, :role)";
		$stmt = $this->conn->prepare($query);

		$hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
		$stmt->bindParam(':username', $data['username']);
		$stmt->bindParam(':password', $hashed_password);
		$stmt->bindParam(':role', $data['role']);

		return $stmt->execute();
	}

	public function findByUsername(string $username): ?array {
		$query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':username', $username);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result ?: null;
	}

	public function authenticate(string $username, string $password): array {
		$user = $this->findByUsername($username);

		if ($user && password_verify($password, $user['password'])) {
			$jwt = new JWT();
			$token = $jwt->generateToken([
				'id' => $user['id'],
				'username' => $user['username'],
				'role' => $user['role']
			]);

			return [
				'success' => true,
				'token' => $token,
				'user' => [
					'id' => $user['id'],
					'username' => $user['username'],
					'role' => $user['role']
				]
			];
		}

		return [
			'success' => false,
			'message' => 'Invalid credentials'
		];
	}
}