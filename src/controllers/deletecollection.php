<?php
require_once("../api.php");
require_once("../controllers/error.php");


/**
 * Deletes a given collection based on its slug
 **/
function deleteCollection($slug) {
	if (!$slug or $slug == 'new') {
		displayError(400, "Invalid collection slug $slug to delete");
	}
	try {
		$response = fetchFromApi("/v2/collections/".urlencode($slug), "DELETE");
		header("Location: /collections/?deleted=collection", true, 303);
	} catch (ApiError $error) {
		displayError(502, "Error deleting collection in API.\n\n".$response);
	}
}