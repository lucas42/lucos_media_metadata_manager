<?php
require_once("../formfields.php");
require_once("../controllers/error.php");

/**
 * Fetches metadata about the given track and displays it in a html form
 */
function viewTrack($trackid) {
	$apiurl = "https://media-api.l42.eu/v2/tracks/${trackid}";
	$response = @file_get_contents($apiurl);
	if ($response === false) {
		$error = error_get_last()["message"];
		if (str_contains($error, "404 Not Found")) {
			displayError(404, "Track ${trackid} Not Found", $trackid);
		} else {
			displayError(502, "Can't fetch track from API.\n\n".$error, $trackid);
		}
		return;
	}
	$data = json_decode($response, true);

	$apiurl = "https://media-api.l42.eu/v2/collections/";
	$response = @file_get_contents($apiurl);
	if ($response === false) {
		$error = error_get_last()["message"];
		displayError(502, "Can't fetch collections from API.\n\n".$error);
		return;
	}
	$collections = json_decode($response, true);
	$form_fields = getFormFields();
	$form_fields["collections"] = [
		"type" => "multiselect",
		"values" => [],
		"hint" => "The collections this track is part of",
	];
	foreach ($collections as $collection) {
		$form_fields["collections"]["values"][$collection["slug"]] = $collection["name"];
	}
	$data["tags"]["collections"] = [];
	foreach ($data["collections"] as $collection) {
		$data["tags"]["collections"][] = $collection["slug"];
	}

	require("../views/track.php");
}