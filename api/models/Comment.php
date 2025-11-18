<?php

namespace App\models;

use PDO;

class Comment extends Model {
	protected string $table_name = 'comments';

	public function create(array $data): bool {
		$query = "INSERT INTO " . $this->table_name . " (ticket_id, user_id, message) VALUES (:ticket_id, :user_id, :message)";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':ticket_id', $data['ticket_id']);
		$stmt->bindParam(':user_id', $data['user_id']);
		$stmt->bindParam(':message', $data['message']);
		return $stmt->execute();
	}

	public function getByTicketId(mixed $ticket_id): array {
		$query = "SELECT c.*, u.username as user_name 
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.user_id = u.id
                  WHERE c.ticket_id = :ticket_id
                  ORDER BY c.created_at";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':ticket_id', $ticket_id);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}

