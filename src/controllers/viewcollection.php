<?php
require_once("../formfields.php");
require_once("../controllers/error.php");
require_once("../controllers/searchtracks.php");

/**
 * Fetches metadata about the given track and displays it in a html form
 */
function viewCollection($slug) {
	$apiurl = "https://media-api.l42.eu/v2/collections/${slug}";
	$response = @file_get_contents($apiurl);
	if ($response === false) {
		$error = error_get_last()["message"];
		if (str_contains($error, "404 Not Found")) {
			displayError(404, "Collection ${slug} Not Found");
		} else {
			displayError(502, "Can't fetch collection from API.\n\n".$error);
		}
	} else {
		$data = json_decode($response, true);
		$tracks = summariseTracks($data["tracks"]);
		require("../views/collection.php");
	}
}

/**
 * Displays a blank html form for adding a new collection
 */
function newCollectionForm() {
	$data = [
		'slug' => null,
		'name' => null,
	];
	$tracks = [];
	require("../views/collection.php");
}