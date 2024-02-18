<?php
require_once("../formfields.php");
require_once("../api.php");
require_once("../controllers/error.php");

/**
 * Fetches metadata about the given track and displays it in a html form
 */
function viewTrack($trackid) {
	try {
		$data = fetchFromApi("/v2/tracks/{$trackid}");
		$form_fields = getFormFields();
		$data["tags"]["collections"] = [];
		foreach ($data["collections"] as $collection) {
			$data["tags"]["collections"][] = $collection["slug"];
		}

		require("../views/track.php");
	} catch (ApiError $error) {
		if ($error->getCode() == 404) {
			displayError(404, "Track {$trackid} Not Found", $trackid);
		} else {
			displayError(502, "Can't fetch track from API.\n\n".$error->getMessage(), $trackid);
		}
	}
}