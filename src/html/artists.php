<?php

require("../conneg.php");

$urlparts = explode('/', $_SERVER['PHP_SELF']);
$artistid = (count($urlparts) > 2 && $urlparts[2] !== '') ? $urlparts[2] : null;

// JSON proxy for the client-side artist search
// (tom-select in form-ui.js calls /artists with Accept: application/json for
// both the search query and the create-on-the-fly POST).
if ($artistid === null && choose_json_over_html()) {
	require_once("../authentication.php");
	require_once("../api.php");

	header('Content-Type: application/json; charset=utf-8');

	$method = $_SERVER['REQUEST_METHOD'];

	if ($method === 'GET') {
		requireScope("media-metadata:read");
		try {
			$params = [];
			if (isset($_GET['q']) && $_GET['q'] !== '') $params[] = 'q=' . urlencode($_GET['q']);
			if (isset($_GET['page']) && $_GET['page'] !== '') $params[] = 'page=' . urlencode($_GET['page']);
			$path = '/v3/artists' . ($params ? '?' . implode('&', $params) : '');
			$data = fetchFromApi($path);
			echo json_encode($data);
		} catch (ApiError $e) {
			http_response_code(502);
			echo json_encode(['error' => $e->getMessage()]);
		}
	} elseif ($method === 'POST') {
		requireScope("media-metadata:write");
		$input = json_decode(file_get_contents('php://input'), true) ?? [];
		$name = trim($input['name'] ?? '');
		try {
			$data = fetchFromApi('/v3/artists', 'POST', ['name' => $name]);
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
	exit;
}

// No HTML artist management UI — this endpoint only serves the JSON proxy for now.
// A full artist management UI (list/view/merge) is out of scope for this issue.
require_once("../controllers/error.php");
displayError(404, "Artist management UI not yet available");
