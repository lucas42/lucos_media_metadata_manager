<?php
require_once("../api.php");
require_once("../controllers/error.php");

/**
 * Fetches a paginated list of albums and renders the HTML list page.
 */
function listAlbums($page) {
	try {
		$data = fetchFromApi("/v3/albums?page={$page}");
		$albums = $data["albums"] ?? [];
		$totalPages = $data["totalPages"] ?? 1;
		$currentPage = $data["page"] ?? 1;
		require("../views/albums.php");
	} catch (ApiError $error) {
		displayError(502, "Can't fetch albums from API.\n\n".$error->getMessage());
	}
}
