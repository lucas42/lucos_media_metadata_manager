<?php
require_once("../api.php");

header("Content-Type: application/json");
$output = [
	"system" => "lucos_media_metadata_manager",
	"title" => "Media Metadata Manager",
	"ci" => [
		"circle" => "gh/lucas42/lucos_media_metadata_manager",
	],
	"checks" => [
		"metadata-api" => [
			"techDetail" => "Can connect to lucos media metadata API",
		]
	],
	"metrics" => [],
];
try {
	$tracks = fetchFromApi("/v3/tracks", timeout: 0.5);
	$output["checks"]["metadata-api"]["ok"] = true;
	$output["metrics"]["track-count"] = [
		"value" => $tracks["totalTracks"] ?? 0,
		"techDetail" => "Total number of tracks in the metadata API",
	];
} catch (ApiError $error) {
	$output["checks"]["metadata-api"]["ok"] = false;
	$output["checks"]["metadata-api"]["debug"] = $error->getMessage();
}
echo json_encode($output);
