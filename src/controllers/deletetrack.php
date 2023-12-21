<?php
require_once("../controllers/error.php");


/**
 * Deletes a given track based on its id
 **/
function deleteTrack($trackid) {
	$trackurl = "https://media-api.l42.eu/v2/tracks/${trackid}";
	$context = stream_context_create([
		"http" => [
			"method" => "DELETE",
			"ignore_errors" => true,
		],
	]);
	
	$response = file_get_contents($trackurl, false, $context);
	if (str_ends_with($http_response_header[0], "204 No Content")) {
		header("Location: /?deleted=track", true, 303);
	} else {
		displayError(502, "Error deleting track in API.\n\n".$response);
	}
}