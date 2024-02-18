<?php
require_once("../api.php");
require_once("../controllers/error.php");

function searchTracks($params, $page) {
	$basequerystring = http_build_query($params);
	$path = "/v2/tracks?${basequerystring}&page=${page}";
	try {
		$data = fetchFromApi($path);
		$tracks = summariseTracks($data["tracks"]);
		$totalPages = $data["totalPages"];

		require_once("../formfields.php");
		$form_fields = getFormFields();
		require("../views/searchresults.php");
	} catch (ApiError $error) {
		displayError(502, "Can't fetch search results from API.\n\n".$error->getMessage());
	}
}

function summariseTracks($tracks) {
	return array_map(function ($track) {
		if (!empty($track["tags"]["title"])) {
			$title = $track["tags"]["title"];

		// If track has no title, base it on URL
		} else {
			// Only look at the filename
			$url_parts = explode("/",$track["url"]);
			$filename = array_pop($url_parts);

			// Strip the extension off the filename
			$filename_parts = explode(".", $filename);
			array_pop($filename_parts);
			$title = implode(".", $filename_parts);
		}

		// Prefix the tile with the artist, if one is given
		if (!empty($track["tags"]["artist"])) {
			$title = $track["tags"]["artist"]." - ".$title;
		}
		return [
			"id" => $track["trackid"],
			"title" => $title,
			"url" => $track["url"],
		];
	}, $tracks);
}

function searchHomepage() {
	require_once("../formfields.php");
	$form_fields = getTagFields();
	require("../views/advancedsearch.php");
}