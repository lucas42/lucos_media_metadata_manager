<?php
require("../authentication.php");
require_once("../api.php");

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
	try {
		$page = $_GET['page'] ?? '';
		$path = '/v3/albums' . ($page !== '' ? '?page=' . urlencode($page) : '');
		$data = fetchFromApi($path);
		echo json_encode($data);
	} catch (ApiError $e) {
		$status = $e->getCode();
		http_response_code($status >= 400 ? $status : 502);
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
		$status = $e->getCode();
		http_response_code($status >= 400 ? $status : 502);
		echo json_encode(['error' => $e->getMessage()]);
	}
} else {
	http_response_code(405);
	header('Allow: GET, POST');
	echo json_encode(['error' => 'Method not allowed']);
}
