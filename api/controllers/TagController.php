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
		$this->sendSuccess(['data' => $tags]);
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

		$tagId = $this->tag->create(['name' => $input['name']]);
		if ($tagId) {
			$tag = $this->tag->getById($tagId);
			$this->sendSuccess(['message' => 'Tag created successfully', 'data' => $tag]);
		} else {
			$this->sendError('Failed to create tag', 500);
		}
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

		if ($this->tag->update($id, ['name' => $input['name']])) {
			$this->sendSuccess(['message' => 'Tag updated successfully']);
		} else {
			$this->sendError('Failed to update tag', 500);
		}
	}

	public function delete(int $id): void {
		AuthMiddleware::requireRole('admin');

		if ($this->tag->delete($id)) {
			$this->sendSuccess(['message' => 'Tag deleted successfully']);
		} else {
			$this->sendError('Failed to delete tag', 500);
		}
	}
}