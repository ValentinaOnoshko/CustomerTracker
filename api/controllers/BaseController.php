<?php

namespace App\controllers;

use JsonException;

abstract class BaseController {
	private static function processJson(callable $operation, callable $errorCallback): mixed {
		try {
			return $operation();
		} catch (JsonException) {
			$errorCallback();
			return null;
		}
	}

	protected function getJsonInput(): ?array {
		$json = file_get_contents('php://input');
		if ($json === false) {
			$this->sendError('Invalid request body');
			return null;
		}

		return self::processJson(
			static function() use ($json) {
				$input = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
				return is_array($input) ? $input : null;
			},
			function() {
				$this->sendError('Invalid JSON format');
			}
		);
	}

	protected function sendSuccess(array $data = []): void {
		$this->sendJson(['success' => true] + $data, 200);
	}

	protected function sendError(string $message, int $code = 400): void {
		$this->sendJson(['error' => $message], $code);
	}

	protected function sendJson(array $data, int $statusCode): void {
		http_response_code($statusCode);
		self::processJson(
			static function() use ($data) {
				echo json_encode($data, JSON_THROW_ON_ERROR);
			},
			static function() {
				echo '{"error": "Internal server error"}';
			}
		);
	}
}