<?php

require("../conneg.php");

$urlparts = explode('/', $_SERVER['PHP_SELF']);
$albumid = (count($urlparts) > 2 && $urlparts[2] !== '') ? $urlparts[2] : null;
$subpath = (count($urlparts) > 3) ? $urlparts[3] : null;

// Content-negotiated RDF redirect for single albums: if the client asks for
// RDF, hand off to the media API. Same pattern as tracks.php — using PHP's
// default redirect status (302), consistent with the existing /tracks/{id}
// redirect for RDF.
if (is_numeric($albumid) && !$subpath && $_SERVER['REQUEST_METHOD'] === 'GET' && choose_rdf_over_html()) {
	header("Location: " . getenv("MEDIA_API") . "/v3/albums/{$albumid}");
	exit;
}

// Preserve the existing JSON proxy behaviour for the client-side album search
// (tom-select in form-ui.js calls /albums with Accept: application/json for
// both the search query and the create-on-the-fly POST).
if ($albumid === null && choose_json_over_html()) {
	require_once("../authentication.php");
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
	exit;
}

require("../authentication.php");
require("../csrf.php");
require("../controllers/listalbums.php");
require("../controllers/viewalbum.php");
require("../controllers/updatealbum.php");
require("../controllers/deletealbum.php");
require("../controllers/mergealbums.php");
require_once("../controllers/error.php");

$page = empty($_GET['page']) ? null : $_GET['page'];
if (!is_numeric($page) || $page < 1) $page = "1";

if ($albumid === "merge") {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		verifyCsrfToken();
		mergeAlbums($_POST);
	} else {
		showMergeAlbums($_GET);
	}
} elseif ($albumid === null) {
	// /albums — list page, or lookup-by-id form redirect
	if (array_key_exists("albumid", $_GET) && $_GET["albumid"] !== "") {
		header("Location: /albums/" . urlencode($_GET["albumid"]));
	} else {
		listAlbums($page);
	}
} elseif (!is_numeric($albumid)) {
	displayError(404, "Need to provide a numerical album id in the URL");
} elseif (!$subpath) {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		verifyCsrfToken();
		updateAlbum($albumid, $_POST);
	} else {
		viewAlbum($albumid, $page);
	}
} elseif ($subpath === "delete") {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		verifyCsrfToken();
		deleteAlbum($albumid);
	} else {
		header("Allow: POST");
		displayError(405, "Delete endpoint needs a POST request");
	}
} else {
	displayError(404, "Album subpath $subpath not recognised");
}
