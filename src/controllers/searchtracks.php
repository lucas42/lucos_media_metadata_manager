<?php
require_once("../controllers/error.php");

/**
 * Fetches metadata about the given track and displays it in a html form
 */
function searchTracks($query) {
	$apiurl = "https://media-api.l42.eu/search?q=".urlencode($query);
	$response = @file_get_contents($apiurl);
	if ($response === false) {
		$error = error_get_last()["message"];
		displayError(502, "Can't fetch search results from API.\n\n".$error);
	} else {
		$data = json_decode($response, true);
		require("../views/search.php");
	}
}