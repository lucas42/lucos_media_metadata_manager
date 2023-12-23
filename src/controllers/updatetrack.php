<?php
require_once("../formfields.php");

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
		$tags[$key] = $postdata[$key];
	}
	$api_data["tags"] = $tags;
	$trackurl = "https://media-api.l42.eu/v2/tracks/${trackid}";
	$context = stream_context_create([
		"http" => [
			"method" => "PATCH",
			"header" => "Content-Type: application/json",
			"content" => json_encode($api_data),
		],
	]);
	file_get_contents($trackurl, false, $context);
	header("Location: /tracks/${trackid}?saved=true", true, 303);
}