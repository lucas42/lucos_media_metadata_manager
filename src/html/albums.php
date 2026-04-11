<?php
require("../authentication.php");
require_once("../api.php");

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
	try {
		$params = [];
		if (isset($_GET['q']) && $_GET['q'] !== '') $params[] = 'q=' . urlencode($_GET['q']);
		if (isset($_GET['page']) && $_GET['page'] !== '') $params[] = 'page=' . urlencode($_GET['page']);
		$path = '/v3/albums' . ($params ? '?' . implode('&', $params) : '');
		$data = fetchFromApi($path);
		echo json_encode($data);
	} catch (ApiError $e) {
		http_response_code(502);
		echo json_encode(['error' => $e->getMessage()]);
	}
} elseif ($method === 'POST') {
	$input = json_decode(file_get_contents('php://input'), true) ?? [];
	$name = trim($input['name'] ?? '');
	try {
		$data = fetchFromApi('/v3/albums', 'POST', ['name' => $name]);
		http_response_code(201);
		echo json_encode($data);
	} catch (ApiError $e) {
		http_response_code(502);
		echo json_encode(['error' => $e->getMessage()]);
	}
} else {
	http_response_code(405);
	header('Allow: GET, POST');
	echo json_encode(['error' => 'Method not allowed']);
}
