<?php

namespace App\controllers;

use App\models\Status;
use App\utils\AuthMiddleware;

class StatusController extends BaseController {
	private Status $status;

	public function __construct() {
		$this->status = new Status();
	}

	public function getAll(): void {
		AuthMiddleware::requireRole('admin');
		$statuses = $this->status->getAll();
		$this->sendSuccess(['data' => $statuses]);
	}

	public function create(): void {
		AuthMiddleware::requireRole('admin');
		$input = $this->getJsonInput();
		if ($input === null) {
			return;
		}

		if (!isset($input['name'])) {
			$this->sendError('Name required');
			return;
		}

		$this->handleStatusOperation(
			$this->status->create(['name' => $input['name']]),
			'Status created successfully',
			'Failed to create status'
		);
	}

	public function update(int $id): void {
		AuthMiddleware::requireRole('admin');
		$input = $this->getJsonInput();
		if ($input === null) {
			return;
		}

		if (!isset($input['name'])) {
			$this->sendError('Name required');
			return;
		}

		$this->handleStatusOperation(
			$this->status->update($id, ['name' => $input['name']]),
			'Status updated successfully',
			'Failed to update status'
		);
	}

	public function delete(int $id): void {
		AuthMiddleware::requireRole('admin');

		$this->handleStatusOperation(
			$this->status->delete($id),
			'Status deleted successfully',
			'Failed to delete status'
		);
	}

	private function handleStatusOperation(bool $success, string $successMessage, string $errorMessage): void {
		if ($success) {
			$this->sendSuccess(['message' => $successMessage]);
		} else {
			$this->sendError($errorMessage, 500);
		}
	}
}