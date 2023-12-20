<?php
require_once("../controllers/error.php");

/**
 * Fetches metadata about the given track and displays it in a html form
 */
function listCollections() {
	$apiurl = "https://media-api.l42.eu/v2/collections/";
	$response = @file_get_contents($apiurl);
	if ($response === false) {
		$error = error_get_last()["message"];
		displayError(502, "Can't fetch collections from API.\n\n".$error);
	} else {
		$collections = json_decode($response, true);
		require("../views/collections.php");
	}
}