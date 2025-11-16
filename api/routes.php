<?php

use App\controllers\AuthController;
use App\controllers\TicketController;
use App\controllers\StatusController;
use App\controllers\TagController;
use App\controllers\BaseController;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(200);
	exit();
}

$request_uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query parameters
$request_uri = parse_url($request_uri, PHP_URL_PATH);
$request_uri = str_replace('/api/', '', $request_uri);

$segments = explode('/', $request_uri);
$resource = $segments[0] ?? '';
$action = $segments[1] ?? null;
$id = $segments[2] ?? null;

$routerController = new class extends BaseController {
	public function getSendError(): callable {
		return [$this, 'sendError'];
	}

	public function getSendJson(): callable {
		return [$this, 'sendJson'];
	}
};

$sendError = $routerController->getSendError();
$sendJson = $routerController->getSendJson();

function handleMethodNotAllowed(callable $sendJson): void {
	$sendJson(['error' => 'Method not allowed'], 405);
}

function handleRouteNotFound(callable $sendJson): void {
	$sendJson(['error' => 'Route not found'], 404);
}

function handleResource($controller, string $action, string $method, callable $sendJson, ?string $id = null): void {
	if ($action === '') {
		if ($method === 'GET') {
			$controller->getAll();
		} elseif ($method === 'POST') {
			$controller->create();
		} else {
			handleMethodNotAllowed($sendJson);
		}
	} elseif ($id) {
		if ($method === 'GET') {
			$controller->getById($id);
		} elseif ($method === 'PUT') {
			$controller->update($id);
		} elseif ($method === 'DELETE') {
			$controller->delete($id);
		} else {
			handleMethodNotAllowed($sendJson);
		}
	} else {
		handleRouteNotFound($sendJson);
	}
}

if ($resource === 'auth') {
	$controller = new AuthController();
	if ($action === 'login' && $method === 'POST') {
		$controller->login();
	} elseif ($action === 'register' && $method === 'POST') {
		$controller->register();
	} elseif ($action === null || $action === '') {
		handleRouteNotFound($sendJson);
	} else {
		handleMethodNotAllowed($sendJson);
	}
} elseif ($resource === 'tickets') {
	$controller = new TicketController();
	handleResource($controller, $action, $method, $sendJson, $id);
} elseif ($resource === 'statuses') {
	$controller = new StatusController();
	handleResource($controller, $action, $method, $sendJson, $id);
} elseif ($resource === 'tags') {
	$controller = new TagController();
	handleResource($controller, $action, $method, $sendJson, $id);
} else {
	handleRouteNotFound($sendJson);
}