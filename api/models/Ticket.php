<?php

namespace App\models;

use PDO;

class Ticket extends Model {
	protected string $table_name = 'tickets';

	public function create(array $data): bool {
		$query = "INSERT INTO " . $this->table_name . " (user_id, title, description, status_id) VALUES (:user_id, :title, :description, :status_id)";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':user_id', $data['user_id']);
		$stmt->bindParam(':title', $data['title']);
		$stmt->bindParam(':description', $data['description']);
		$stmt->bindParam(':status_id', $data['status_id']);
		return $stmt->execute();
	}

	public function update(mixed $id, array $data): bool {
		$query = "UPDATE " . $this->table_name . " SET title = :title, description = :description, status_id = :status_id WHERE id = :id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':title', $data['title']);
		$stmt->bindParam(':description', $data['description']);
		$stmt->bindParam(':status_id', $data['status_id']);
		return $stmt->execute();
	}

	public function getByUserId(mixed $user_id): array {
		$query = "SELECT t.*, s.name as status_name, u.username as user_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN statuses s ON t.status_id = s.id
                  LEFT JOIN users u ON t.user_id = u.id
                  WHERE t.user_id = :user_id
                  ORDER BY t.created_at DESC";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getAllWithDetails(): array {
		$query = "SELECT t.*, s.name as status_name, u.username as user_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN statuses s ON t.status_id = s.id
                  LEFT JOIN users u ON t.user_id = u.id
                  ORDER BY t.created_at DESC";
		return $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function addTag(mixed $ticket_id, mixed $tag_id): bool {
		$query = "INSERT INTO ticket_tags (ticket_id, tag_id) VALUES (:ticket_id, :tag_id)";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':ticket_id', $ticket_id);
		$stmt->bindParam(':tag_id', $tag_id);
		return $stmt->execute();
	}

	public function removeTag(mixed $ticket_id, mixed $tag_id): bool {
		$query = "DELETE FROM ticket_tags WHERE ticket_id = :ticket_id AND tag_id = :tag_id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':ticket_id', $ticket_id);
		$stmt->bindParam(':tag_id', $tag_id);
		return $stmt->execute();
	}

	public function getTagsByTicketId(mixed $ticket_id): array {
		$query = "SELECT tg.name FROM ticket_tags tt 
                  JOIN tags tg ON tt.tag_id = tg.id 
                  WHERE tt.ticket_id = :ticket_id";
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':ticket_id', $ticket_id);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}
}