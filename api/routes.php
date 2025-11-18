<?php

use App\controllers\AuthController;
use App\controllers\TicketController;
use App\controllers\StatusController;
use App\controllers\TagController;
use App\controllers\CommentController;
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

$request_uri = parse_url($request_uri, PHP_URL_PATH);
$request_uri = str_replace('/api/', '', $request_uri);

$segments = explode('/', $request_uri);
$resource = $segments[0] ?? '';
$action = $segments[1] ?? null;
$id = $segments[2] ?? null;

$isSpecialRoute = isset($segments[2]) && ($segments[2] === 'tags' || $segments[2] === 'ticket');
if (is_numeric($action) && !$isSpecialRoute) {
	$id = $action;
	$action = null;
}

$routerController = new class extends BaseController {
	public function sendErrorWrapper(string $message, int $code = 400): void {
		$this->sendError($message, $code);
	}

	public function sendJsonWrapper(array $data, int $statusCode): void {
		$this->sendJson($data, $statusCode);
	}
};

$sendError = [$routerController, 'sendErrorWrapper'];
$sendJson = [$routerController, 'sendJsonWrapper'];

function handleMethodNotAllowed(callable $sendJson): void {
	$sendJson(['error' => 'Method not allowed'], 405);
}

function handleRouteNotFound(callable $sendJson): void {
	$sendJson(['error' => 'Route not found'], 404);
}

function handleResource($controller, ?string $action, string $method, callable $sendJson, ?string $id = null): void {
	if ($id !== null && $id !== '') {
		if ($method === 'GET') {
			$controller->getById($id);
		} elseif ($method === 'PUT') {
			$controller->update($id);
		} elseif ($method === 'DELETE') {
			$controller->delete($id);
		} else {
			handleMethodNotAllowed($sendJson);
		}
	} elseif ($action === null || $action === '') {
		if ($method === 'GET') {
			$controller->getAll();
		} elseif ($method === 'POST') {
			$controller->create();
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
	if (isset($segments[2]) && $segments[2] === 'tags' && is_numeric($action)) {
		$ticket_id = (int)$action;
		if ($method === 'POST') {
			$controller->addTag($ticket_id);
		} elseif ($method === 'DELETE' && isset($segments[3])) {
			$controller->removeTag($ticket_id, (int)$segments[3]);
		} else {
			handleMethodNotAllowed($sendJson);
		}
	} else {
		handleResource($controller, $action, $method, $sendJson, $id);
	}
} elseif ($resource === 'statuses') {
	$controller = new StatusController();
	handleResource($controller, $action, $method, $sendJson, $id);
} elseif ($resource === 'tags') {
	$controller = new TagController();
	handleResource($controller, $action, $method, $sendJson, $id);
} elseif ($resource === 'comments') {
	$controller = new CommentController();
	if ($action === 'ticket' && $id && $method === 'GET') {
		$controller->getByTicketId((int)$id);
	} elseif ($action === null || $action === '') {
		if ($method === 'POST') {
			$controller->create();
		} else {
			handleMethodNotAllowed($sendJson);
		}
	} else {
		handleRouteNotFound($sendJson);
	}
} else {
	handleRouteNotFound($sendJson);
}