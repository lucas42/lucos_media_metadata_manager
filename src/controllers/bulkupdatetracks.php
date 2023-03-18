<?php
require_once("../formfields.php");

/**
 * Updates the metadata of tracks matching the given search paramaters
 * Sets the value of each field to the value in $postdata for that key
 **/
function bulkUpdateTracks($params, $page, $postdata) {
	if (!is_numeric($page) or $page < 1) $page = "1";
	$basequerystring = http_build_query($params);
	$apiurl = "https://media-api.l42.eu/v2/tracks?${basequerystring}&page=${page}";

	$tags = array();
	foreach (getFormKeys() as $key) {
		if (!empty($postdata[$key])) {
			$tags[$key] = $postdata[$key];
		}
	}
	$context = stream_context_create([
		"http" => [
			"method" => "PATCH",
			"header" => "Content-Type: application/json",
			"content" => json_encode(["tags" => $tags]),
		],
	]);
	file_get_contents($apiurl, false, $context);
	header("Location: /search?${basequerystring}&page=${page}&saved=true", true, 303);
}