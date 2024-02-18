<?php
require_once("../api.php");
require_once("../controllers/error.php");

/**
 * Fetches metadata about the given track and displays it in a html form
 */
function listCollections() {
	try {
		$collections = fetchFromApi("/v2/collections");
		require("../views/collections.php");
	} catch (ApiError $error) {
		displayError(502, "Can't fetch collections from API.\n\n".$error->getMessage());
	}
}