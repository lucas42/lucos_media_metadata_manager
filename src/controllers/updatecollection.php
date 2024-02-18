<?php
require_once("../formfields.php");
require_once("../api.php");
require_once("../controllers/error.php");


/**
 * Updates the metadata of a given collection based on its slug
 * Sets the value to of each field to the value in $postdata for that key
 **/
function updateCollection($slug, $postdata) {
	if (!$slug or $slug == 'new') {
		$slug = $postdata['slug'];
	}

	try {
		$collections = fetchFromApi("/v2/collections/".urlencode($slug), "PUT", $postdata);
		header("Location: /collections/${slug}?saved=true", true, 303);
	} catch (ApiError $error) {
		displayError(502, "Error updating collection in API.\n\n".$error->getMessage());
	}
}