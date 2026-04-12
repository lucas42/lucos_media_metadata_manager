<?php
require_once("../api.php");
require_once("../controllers/error.php");

/**
 * Deletes an album by id and redirects back to the album list.
 *
 * The API returns 409 if any tracks still reference the album — in that case,
 * show a clear message to the user rather than a generic 502.
 */
function deleteAlbum($albumid) {
	if (!is_numeric($albumid)) {
		displayError(400, "Invalid album id to delete");
		return;
	}
	try {
		fetchFromApi("/v3/albums/".urlencode($albumid), "DELETE");
		header("Location: /albums?deleted=album", true, 303);
	} catch (ApiError $error) {
		if ($error->getCode() == 409) {
			displayError(409, "Can't delete this album — one or more tracks still reference it. Remove the album from those tracks first.");
		} else if ($error->getCode() == 404) {
			displayError(404, "Album {$albumid} Not Found");
		} else {
			displayError(502, "Error deleting album in API.\n\n".$error->getMessage());
		}
	}
}
