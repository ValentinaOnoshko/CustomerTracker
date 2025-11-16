<?php

namespace App\models;

use App\config\Database;
use PDO;

abstract class Model {
	protected ?PDO $conn;
	protected string $table_name;

	public function __construct() {
		$database = new Database();
		$this->conn = $database->getConnection();
	}

	public function getAll(): array {
		$query = "SELECT * FROM " . $this->table_name;
		return $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getById(mixed $id): array|false {
		$query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function delete(mixed $id): bool {
		$query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':id', $id);
		return $stmt->execute();
	}
}