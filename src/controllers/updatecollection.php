<?php
require_once("../formfields.php");
require_once("../controllers/error.php");


/**
 * Updates the metadata of a given collection based on its slug
 * Sets the value to of each field to the value in $postdata for that key
 **/
function updateCollection($slug, $postdata) {
	if (!$slug or $slug == 'new') {
		$slug = $postdata['slug'];
	}

	$collectionurl = "https://media-api.l42.eu/v2/collections/${slug}";
	$context = stream_context_create([
		"http" => [
			"method" => "PUT",
			"header" => "Content-Type: application/json",
			"content" => json_encode($postdata),
			"ignore_errors" => true,
		],
	]);
	
	$response = file_get_contents($collectionurl, false, $context);
	if (str_ends_with($http_response_header[0], "200 OK")) {
		header("Location: /collections/${slug}?saved=true", true, 303);
	} elseif (str_ends_with($http_response_header[0], "400 Bad Request")) {
		displayError(400, $response);
	} else {
		displayError(502, "Error updating collection in API.\n\n".$respnose);
	}
}