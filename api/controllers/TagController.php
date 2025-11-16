<?php

namespace App\controllers;

use App\models\Tag;
use App\utils\AuthMiddleware;

class TagController extends BaseController {
	private Tag $tag;

	public function __construct() {
		$this->tag = new Tag();
	}

	public function getAll(): void {
		AuthMiddleware::requireRole('admin');
		$tags = $this->tag->getAll();
		$this->sendJson(['success' => true, 'data' => $tags], 200);
	}

	public function create(): void {
		AuthMiddleware::requireRole('admin');
		$input = $this->getJsonInput();
		if ($input === null) {
			return;
		}

		if (!isset($input['name'])) {
			$this->sendJson(['error' => 'Name required'], 400);
			return;
		}

		if ($this->tag->create(['name' => $input['name']])) {
			$this->sendJson(['success' => true, 'message' => 'Tag created successfully'], 200);
		} else {
			$this->sendJson(['error' => 'Failed to create tag'], 500);
		}
	}

	public function update(int $id): void {
		AuthMiddleware::requireRole('admin');
		$input = $this->getJsonInput();
		if ($input === null) {
			return;
		}

		if (!isset($input['name'])) {
			$this->sendJson(['error' => 'Name required'], 400);
			return;
		}

		if ($this->tag->update($id, ['name' => $input['name']])) {
			$this->sendJson(['success' => true, 'message' => 'Tag updated successfully'], 200);
		} else {
			$this->sendJson(['error' => 'Failed to update tag'], 500);
		}
	}

	public function delete(int $id): void {
		AuthMiddleware::requireRole('admin');

		if ($this->tag->delete($id)) {
			$this->sendJson(['success' => true, 'message' => 'Tag deleted successfully'], 200);
		} else {
			$this->sendJson(['error' => 'Failed to delete tag'], 500);
		}
	}
}