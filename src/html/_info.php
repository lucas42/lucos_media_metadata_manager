<?php
require_once("../api.php");

header("Content-Type: application/json");
$output = [
	"system" => "lucos_media_metadata_manager",
	"ci" => [
		"circle" => "gh/lucas42/lucos_media_metadata_manager",
	],
	"checks" => [
		"metadata-api" => [
			"techDetail" => "Can connect to lucos media metadata API",
		]
	],
];
try {
	fetchFromApi("/v2/tracks");
	$output["checks"]["metadata-api"]["ok"] = true;
} catch (ApiError $error) {
	$output["checks"]["metadata-api"]["ok"] = false;
	$output["checks"]["metadata-api"]["debug"] = $error->getMessage();
}
echo json_encode($output);
