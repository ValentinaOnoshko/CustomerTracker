<?php

if (str_starts_with($_SERVER['REQUEST_URI'], '/api/')) {
	$api_path = substr($_SERVER['REQUEST_URI'], 4);
	$_SERVER['REQUEST_URI'] = $api_path;
	include __DIR__ . '/../api/index.php';
} else {
	$vue_index = __DIR__ . '/../frontend/dist/index.html';
	if (file_exists($vue_index)) {
		readfile($vue_index);
	} else {
		echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Ticket Tracker</title>
    <script type="module" src="http://localhost:5173/@vite/client"></script>
    <script type="module" src="http://localhost:5173/src/main.js"></script>
</head>
<body>
    <div id="app"></div>
</body>
</html>';
	}
}