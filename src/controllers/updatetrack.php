<?php
require_once("../formfields.php");

/**
 * Updates the metadata of a given track id
 * Sets the value to of each field to the value in $postdata for that key UNLESS there exists a key for that field suffixed with "_null", in which case the field is deleted
 **/
function updateTrack($trackid, $postdata) {
	$tags = array();
	foreach (getFormKeys() as $key) {
		$tags[$key] = array_key_exists("${key}_null", $postdata) ? null : $postdata[$key];
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