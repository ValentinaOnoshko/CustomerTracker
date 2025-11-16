<?php

namespace App\controllers;

use App\models\Ticket;
use App\utils\AuthMiddleware;

class TicketController extends BaseController {
	private Ticket $ticket;

	public function __construct() {
		$this->ticket = new Ticket();
	}

	public function getAll(): void {
		$user = AuthMiddleware::authenticate();
		$tickets = $user['role'] === 'admin'
			? $this->ticket->getAllWithDetails()
			: $this->ticket->getByUserId($user['id']);
		$this->sendSuccess(['data' => $tickets]);
	}

	public function getById(int $id): void {
		$user = AuthMiddleware::authenticate();
		$ticket = $this->ticket->getById($id);

		if (!$ticket) {
			$this->sendError('Ticket not found', 404);
			return;
		}

		if ($user['role'] !== 'admin' && $ticket['user_id'] !== $user['id']) {
			$this->sendError('Access denied', 403);
			return;
		}

		$ticket['tags'] = $this->ticket->getTagsByTicketId($id);
		$this->sendSuccess(['data' => $ticket]);
	}

	public function create(): void {
		$user = AuthMiddleware::authenticate();
		$input = $this->getJsonInput();
		if ($input === null) {
			return;
		}

		if (!isset($input['title'], $input['description'])) {
			$this->sendError('Title and description required');
			return;
		}

		$data = [
			'user_id' => $user['id'],
			'title' => $input['title'],
			'description' => $input['description'],
			'status_id' => 1
		];

		$success = $this->ticket->create($data);
		$this->handleResponse($success, 'Ticket created successfully', 'Failed to create ticket');
	}

	public function update(int $id): void {
		AuthMiddleware::requireRole('admin');
		$input = $this->getJsonInput();
		if ($input === null) {
			return;
		}

		if (!isset($input['title'], $input['description'], $input['status_id'])) {
			$this->sendError('Title, description, and status_id required');
			return;
		}

		$data = [
			'title' => $input['title'],
			'description' => $input['description'],
			'status_id' => $input['status_id']
		];

		$success = $this->ticket->update($id, $data);
		$this->handleResponse($success, 'Ticket updated successfully', 'Failed to update ticket');
	}

	private function handleResponse(bool $success, string $successMessage, string $errorMessage): void {
		if ($success) {
			$this->sendSuccess(['message' => $successMessage]);
		} else {
			$this->sendError($errorMessage, 500);
		}
	}
}