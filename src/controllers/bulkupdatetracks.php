<?php
require_once("../formfields.php");

/**
 * Updates the metadata of tracks matching the given search paramaters
 * Sets the value of each field to the value in $postdata for that key
 **/
function bulkUpdateTracks($params, $currentpage, $postdata) {
	$basequerystring = http_build_query($params);
	$targetPage = !empty($postdata['page']) ? $postdata['page'] : $currentpage;
	$apiurl = "https://media-api.l42.eu/v2/tracks?${basequerystring}&page=${targetPage}";

	$tags = array();
	foreach (getFormKeys() as $key) {
		if (!is_null($postdata[$key]) and $postdata[$key] !== "") {
			$tags[$key] = $postdata[$key];
		}
		if (!empty($postdata["${key}_blank"])) {
			$tags[$key] = "";
		}
	}
	$headers = ["Content-Type: application/json"];
	if (!empty($postdata['missing-only'])) {
		array_push($headers, "If-None-Match: *");
	}
	$context = stream_context_create([
		"http" => [
			"method" => "PATCH",
			"header" => $headers,
			"content" => json_encode(["tags" => $tags]),
		],
	]);
	file_get_contents($apiurl, false, $context);
	header("Location: /search?${basequerystring}&page=${currentpage}&saved=true", true, 303);
}