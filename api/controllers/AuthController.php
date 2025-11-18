<?php

namespace App\controllers;

use App\models\User;

class AuthController extends BaseController {
	private User $user;

	public function __construct() {
		$this->user = new User();
	}

	public function login(): void {
		$input = $this->getJsonInput();
		if (!$this->validateLoginFields($input)) {
			return;
		}

		$result = $this->user->authenticate($input['username'], $input['password']);

		if ($result['success']) {
			$this->sendSuccess([
				'token' => $result['token'],
				'user' => $result['user']
			]);
		} else {
			$this->sendError($result['message'], 401);
		}
	}

	public function register(): void {
		$input = $this->getJsonInput();
		if (!$this->validateRegistrationFields($input)) {
			return;
		}

		$existingUser = $this->user->findByUsername($input['username']);
		if ($existingUser) {
			$this->sendError('Username already exists', 409);
			return;
		}

		if (strtolower($input['username']) === 'admin') {
			$this->sendError('Username "admin" is reserved');
			return;
		}

		$userData = [
			'username' => $input['username'],
			'password' => $input['password'],
			'role' => 'client'
		];

		if ($this->user->create($userData)) {
			$result = $this->user->authenticate($input['username'], $input['password']);
			if ($result['success']) {
				$this->sendSuccess([
					'message' => 'User created successfully',
					'token' => $result['token'],
					'user' => $result['user']
				]);
			} else {
				$this->sendSuccess(['message' => 'User created successfully. Please login.']);
			}
		} else {
			$this->sendError('Failed to create user', 500);
		}
	}

	private function validateLoginFields(?array $input): bool {
		if ($input === null) {
			return false;
		}

		if (!isset($input['username'], $input['password'])) {
			$this->sendError('Username and password required');
			return false;
		}
		return true;
	}

	private function validateRegistrationFields(?array $input): bool {
		if ($input === null) {
			return false;
		}

		if (!isset($input['username'], $input['password'])) {
			$this->sendError('Username and password required');
			return false;
		}
		return true;
	}
}
