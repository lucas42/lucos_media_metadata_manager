<?php
require_once("../formfields.php");

/**
 * Fetches metadata about the given track and displays it in a html form
 */
function viewTrack($trackid) {
	$apiurl = "https://media-api.l42.eu/tracks/${trackid}";
	$response = file_get_contents($apiurl);
	if ($response === false) {
		$error = error_get_last()["message"];
		if (str_contains($error, "404 Not Found")) {
			http_response_code(404);
			echo "Track ${trackid} Not Found";
			exit;
		}
		http_response_code(502);
		echo "Can't fetch track from API.\n\n".$error["message"];
		exit;
	}
	$data = json_decode($response, true);
	$form_fields = getFormFields();
	require("../views/track.php");
}