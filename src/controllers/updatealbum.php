<?php
require_once("../api.php");
require_once("../controllers/error.php");

/**
 * Updates an album's name via the media API and redirects back to the album
 * page so the user sees the saved state.
 */
function updateAlbum($albumid, $postdata) {
	$name = isset($postdata["name"]) ? trim($postdata["name"]) : "";
	if ($name === "") {
		displayError(400, "Album name can't be empty");
		return;
	}
	try {
		fetchFromApi("/v3/albums/".urlencode($albumid), "PUT", ["name" => $name]);
		header("Location: /albums/{$albumid}?saved=true", true, 303);
	} catch (ApiError $error) {
		if ($error->getCode() == 409) {
			displayError(409, "An album with that name already exists.");
		} else if ($error->getCode() == 404) {
			displayError(404, "Album {$albumid} Not Found");
		} else {
			displayError(502, "Error updating album in API.\n\n".$error->getMessage());
		}
	}
}
