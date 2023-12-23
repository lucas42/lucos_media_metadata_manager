<?php
require_once("../formfields.php");

/**
 * Updates the metadata of tracks matching the given search paramaters
 * Sets the value of each field to the value in $postdata for that key
 **/
function bulkUpdateTracks($params, $currentpage, $postdata) {
	$basequerystring = http_build_query($params);
	$targetPage = !empty($postdata['page']) ? $postdata['page'] : $currentpage;
	$apiurl = "https://media-api.l42.eu/v2/tracks?{$basequerystring}&page={$targetPage}";

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
			$tags[$key] = $postdata[$key];
		}
		if (!empty($postdata["{$key}_blank"])) {
			$tags[$key] = "";
		}
	}
	if (!empty($tags)) $api_data["tags"] = $tags; // Avoid including an empty associative array, as php's json will encode it as an array, not an object

	$headers = ["Content-Type: application/json"];
	if (!empty($postdata['missing-only'])) {
		array_push($headers, "If-None-Match: *");
	}
	$context = stream_context_create([
		"http" => [
			"method" => "PATCH",
			"header" => $headers,
			"content" => json_encode($api_data),
			"ignore_errors" => true,
		],
	]);
	$response = file_get_contents($apiurl, false, $context);
	if (!str_ends_with($http_response_header[0], "200 OK")) {
		throw new Exception("Failed to bulk update tracks in API.\n\n{$error}\n\n{$response}", 502);
	}
	header("Location: /search?{$basequerystring}&page={$currentpage}&saved=true", true, 303);
}