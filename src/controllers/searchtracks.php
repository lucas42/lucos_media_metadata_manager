<?php
require_once("../controllers/error.php");

/**
 * Fetches metadata about the given track and displays it in a html form
 */
function searchTracks($query, $page) {
	if (!is_numeric($page) or $page < 1) $page = "1";
	$apiurl = "https://media-api.l42.eu/search?q=".urlencode($query)."&page=".$page;
	$response = @file_get_contents($apiurl);
	if ($response === false) {
		$error = error_get_last()["message"];
		displayError(502, "Can't fetch search results from API.\n\n".$error);
	} else {
		$data = json_decode($response, true);

		$tracks = array_map(function ($track) {

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
		}, $data["tracks"]);

		require("../views/search.php");
	}
}