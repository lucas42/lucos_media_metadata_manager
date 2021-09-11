<?php
require_once("../formfields.php");

/**
 * Updates the metadata of a given track id
 * Sets the value to of each field to the value in $postdata for that key
 **/
function updateTrack($trackid, $postdata) {
	$tags = array();
	foreach (getFormKeys() as $key) {
		$tags[$key] = $postdata[$key];
	}
	$trackurl = "https://media-api.l42.eu/tracks/${trackid}";
	$context = stream_context_create([
		"http" => [
			"method" => "PATCH",
			"header" => "Content-Type: application/json",
			"content" => json_encode(["tags" => $tags]),
		],
	]);
	file_get_contents($trackurl, false, $context);
	header("Location: /tracks/${trackid}?saved=true", true, 303);
}