<?php
require_once("../api.php");
require_once("../controllers/error.php");
require_once("../controllers/searchtracks.php");

/**
 * Fetches metadata about the given track and displays it in a html form
 */
function viewCollection($slug, $page) {
	try {
		$data = fetchFromApi("/v3/collections/".urlencode($slug)."?page={$page}");
		$tracks = summariseTracks($data["tracks"]);
		$totalPages = $data["totalPages"];
		require("../views/collection.php");
	} catch (ApiError $error) {
		if ($error->getCode() == 404) {
			displayError(404, "Collection {$slug} Not Found");
		} else {
			displayError(apiErrorToManagerStatus($error), apiErrorMessage($error, "Can't fetch collection from API."));
		}
	}
}

/**
 * Displays a blank html form for adding a new collection
 */
function newCollectionForm() {
	$data = [
		'slug' => null,
		'name' => null,
		'icon' => null,
	];
	$tracks = [];
	require("../views/collection.php");
}