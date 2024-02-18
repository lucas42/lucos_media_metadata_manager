<?php
require_once("../formfields.php");
require_once("../api.php");

/**
 * Updates the metadata of a given track id
 * Sets the value to of each field to the value in $postdata for that key
 **/
function updateTrack($trackid, $postdata) {
	$api_data = array();
	$api_data["collections"] = [];
	if (isset($postdata["collections"])) {
		foreach($postdata["collections"] as $slug) {
			$api_data["collections"][] = [
				"slug" => $slug,
			];
		}
		unset($postdata["collections"]);
	}
	$tags = array();
	foreach (getTagKeys() as $key) {
		if (is_array($postdata[$key])) $tags[$key] = implode(",", $postdata[$key]);
		else $tags[$key] = $postdata[$key];
	}
	$api_data["tags"] = $tags;

	try {
		$collections = fetchFromApi("/v2/tracks/${trackid}", "PATCH", $api_data);
		header("Location: /tracks/${trackid}?saved=true", true, 303);
	} catch (ApiError $error) {
		displayError(502, "Error updating track in API.\n\n".$error->getMessage());
	}
}