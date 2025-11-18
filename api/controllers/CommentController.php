<?php

namespace App\controllers;

use App\models\Comment;
use App\utils\AuthMiddleware;
use Exception;

class CommentController extends BaseController {
	private Comment $comment;

	public function __construct() {
		$this->comment = new Comment();
	}

	public function getByTicketId(int $ticket_id): void {
		AuthMiddleware::authenticate();
		$comments = $this->comment->getByTicketId($ticket_id);
		$this->sendSuccess(['data' => $comments]);
	}

	public function create(): void {
		$user = AuthMiddleware::authenticate();
		$input = $this->getJsonInput();
		if ($input === null) {
			return;
		}

		if (!isset($input['ticket_id'], $input['message'])) {
			$this->sendError('Ticket ID and message required');
			return;
		}

		if ($user['role'] !== 'admin') {
			$this->sendError('Only admins can create comments', 403);
			return;
		}

		$data = [
			'ticket_id' => $input['ticket_id'],
			'user_id' => $user['id'],
			'message' => $input['message']
		];

		try {
			$success = $this->comment->create($data);
			if ($success) {
				$this->sendSuccess(['message' => 'Comment created successfully']);
			} else {
				$this->sendError('Failed to create comment', 500);
			}
		} catch (Exception $e) {
			if (str_contains($e->getMessage(), "doesn't exist")) {
				$this->sendError('Comments table does not exist. Please create it using the migration script.', 500);
			} else {
				$this->sendError('Failed to create comment: ' . $e->getMessage(), 500);
			}
		}
	}
}

