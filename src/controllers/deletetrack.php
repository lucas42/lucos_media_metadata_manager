<?php
require_once("../api.php");
require_once("../controllers/error.php");


/**
 * Deletes a given track based on its id
 **/
function deleteTrack($trackid) {
	try {
		$response = fetchFromApi("/v2/tracks/${trackid}", "DELETE");
		header("Location: /?deleted=track", true, 303);
	} catch (ApiError $error) {
		displayError(502, "Error deleting track in API.\n\n".$response);
	}
}