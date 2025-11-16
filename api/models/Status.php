<?php

namespace App\models;

class Status extends Model {
	protected string $table_name = 'statuses';

	public function create(array $data): bool {
		$query = "INSERT INTO " . $this->table_name . " (name) VALUES (:name)";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':name', $data['name']);
		return $stmt->execute();
	}

	public function update(mixed $id, array $data): bool {
		$query = "UPDATE " . $this->table_name . " SET name = :name WHERE id = :id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':name', $data['name']);
		return $stmt->execute();
	}
}