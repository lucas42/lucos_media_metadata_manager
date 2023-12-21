<?php
require_once("../controllers/error.php");


/**
 * Deletes a given collection based on its slug
 **/
function deleteCollection($slug) {
	if (!$slug or $slug == 'new') {
		displayError(400, "Invalid collection slug $slug to delete");
	}

	$collectionurl = "https://media-api.l42.eu/v2/collections/${slug}";
	$context = stream_context_create([
		"http" => [
			"method" => "DELETE",
			"ignore_errors" => true,
		],
	]);
	
	$response = file_get_contents($collectionurl, false, $context);
	if (str_ends_with($http_response_header[0], "204 No Content")) {
		header("Location: /collections/?deleted=true", true, 303);
	} else {
		displayError(502, "Error deleting collection in API.\n\n".$response);
	}
}