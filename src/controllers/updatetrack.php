<?php
require_once("../formfields.php");

/**
 * Updates the metadata of a given track id
 * Sets the value to of each field to the value in $postdata for that key UNLESS there exists a key for that field suffixed with "_null", in which case the field is deleted
 **/
function updateTrack($trackid, $postdata) {
	foreach (getFormFields() as $key => $type) {

		// If a tag is marked as null, DELETE it from the API
		// Otherwise, update the value using PUT
		$method = array_key_exists("${key}_null", $postdata) ? "DELETE" : "PUT";
		$val = $postdata[$key];
		$tagurl = "https://media-api.l42.eu/tags/${trackid}/${key}";
		$context = stream_context_create([
			"http" => [
				"method" => $method,
				"header" => "Content-Type: text/plain",
				"content" => $val,
			],
		]);
		file_get_contents($tagurl, false, $context);
	}
	header("Location: /tracks/${trackid}?saved=true", true, 303);
}