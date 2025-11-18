<?php

namespace App\models;

class Tag extends Model {
	protected string $table_name = 'tags';

	public function create(array $data): int|false {
		$query = "INSERT INTO " . $this->table_name . " (name) VALUES (:name)";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':name', $data['name']);
		if ($stmt->execute()) {
			return (int)$this->conn->lastInsertId();
		}
		return false;
	}

	public function update(mixed $id, array $data): bool {
		$query = "UPDATE " . $this->table_name . " SET name = :name WHERE id = :id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':name', $data['name']);
		return $stmt->execute();
	}
}