<?php
require_once("../formfields.php");
require_once("../api.php");

/**
 * Updates the metadata of tracks matching the given search paramaters
 * Sets the value of each field to the value in $postdata for that key
 **/
function bulkUpdateTracks($params, $currentpage, $postdata) {
	$basequerystring = http_build_query($params);
	$targetPage = !empty($postdata['page']) ? $postdata['page'] : $currentpage;
	$path = "/v2/tracks?{$basequerystring}&page={$targetPage}";

	$api_data = array();
	if (isset($postdata["collections"])) {
		$api_data["collections"] = [];
		foreach($postdata["collections"] as $slug) {
			$api_data["collections"][] = [
				"slug" => $slug,
			];
		}
	}
	if (!empty($postdata["collections_blank"])) {
		$api_data["collections"] = [];
	}

	$tags = array();
	foreach (getTagKeys() as $key) {
		if (!is_null($postdata[$key]) and $postdata[$key] !== "") {
			if (is_array($postdata[$key])) $tags[$key] = implode(",", $postdata[$key]);
			else $tags[$key] = $postdata[$key];
		}
		if (!empty($postdata["{$key}_blank"])) {
			$tags[$key] = "";
		}
	}
	if (!empty($tags)) $api_data["tags"] = $tags; // Avoid including an empty associative array, as php's json will encode it as an array, not an object

	$headers = [];
	if (!empty($postdata['missing-only'])) {
		$headers[] = "If-None-Match: *";
	}
	try {
		fetchFromApi($path, "PATCH", $api_data, $headers);
	} catch (ApiError $error) {
		throw new Exception("Failed to bulk update tracks in API.\n\n{$error}\n\n{$response}", 502);
	}
	header("Location: /search?{$basequerystring}&page={$currentpage}&saved=true", true, 303);
}