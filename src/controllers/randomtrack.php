<?php
require_once("../api.php");
require_once("../controllers/error.php");

/**
 * Gets a random track from the API and redirects the user to that track page
 */
function pickRandomTrack() {
	try {
		$data = fetchFromApi("/v2/tracks/random");
		$trackid = $data["tracks"][0]["trackid"]; // Just look at the first track in the list
		header("Location: /tracks/{$trackid}");
	} catch (ApiError $error) {
		displayError(502, "Can't fetch random tracks from API.\n\n".$error->getMessage());
	}
}