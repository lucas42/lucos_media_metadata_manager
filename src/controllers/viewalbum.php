<?php
require_once("../api.php");
require_once("../controllers/error.php");
require_once("../controllers/searchtracks.php");

/**
 * Fetches metadata for a single album, and any tracks tagged with it, then
 * renders the album HTML page.
 *
 * Tracks are found by querying /v3/tracks with a p.album.uri filter, which
 * is more reliable than name-based search since URIs are stable identifiers.
 * The frontend deliberately does no re-ordering or filtering of the list —
 * whatever order the API returns is what the user sees.
 */
function viewAlbum($albumid, $page) {
	try {
		$album = fetchFromApi("/v3/albums/".urlencode($albumid));
	} catch (ApiError $error) {
		if ($error->getCode() == 404) {
			displayError(404, "Album {$albumid} Not Found");
		} else {
			displayError(502, "Can't fetch album from API.\n\n".$error->getMessage());
		}
		return;
	}

	$tracks = [];
	$totalPages = 1;
	$currentPage = 1;
	try {
		$params = http_build_query([
			"p.album.uri" => $album["uri"],
			"page"        => $page,
		]);
		$trackData = fetchFromApi("/v3/tracks?{$params}");
		$tracks = summariseTracks($trackData["tracks"] ?? []);
		$totalPages = $trackData["totalPages"] ?? 1;
		$currentPage = $trackData["page"] ?? 1;
	} catch (ApiError $error) {
		// Don't block the album page if the track lookup fails — show the
		// album metadata and a note about the track list being unavailable.
		$tracksError = $error->getMessage();
	}

	require("../views/album.php");
}
