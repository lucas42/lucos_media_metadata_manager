<?php
require_once("../controllers/error.php");

/**
 * Gets a random track from the API and redirects the user to that track page
 */
function pickRandomTrack() {
	$apiurl = "https://media-api.l42.eu/tracks/random";
	$response = file_get_contents($apiurl);
	if ($response === false) {
		$error = error_get_last()["message"];
		displayError(502, "Can't fetch random tracks from API.\n\n".$error);
	} else {
		$data = json_decode($response, true);
		$trackid = $data[0]["trackid"]; // Just look at the first track in the list
		header("Location: /tracks/${trackid}");
	}
}