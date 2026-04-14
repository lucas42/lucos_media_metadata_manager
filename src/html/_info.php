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
			"dependsOn" => "lucos_media_metadata_api",
		]
	],
	"metrics" => (object)[],
];
try {
	fetchFromApi("/v3/tracks/1", timeout: 0.5);
	$output["checks"]["metadata-api"]["ok"] = true;
} catch (ApiError $error) {
	$output["checks"]["metadata-api"]["ok"] = false;
	$output["checks"]["metadata-api"]["debug"] = $error->getMessage();
}
echo json_encode($output);
