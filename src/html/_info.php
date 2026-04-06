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
	fetchFromApi("/v3/tracks/1", timeout: 0.5);
	$output["checks"]["metadata-api"]["ok"] = true;
} catch (ApiError $error) {
	// A non-zero code means the API responded with an HTTP status (e.g. 404 if
	// track 1 was deleted) — the API is reachable, so the check still passes.
	// A zero code means a network/timeout failure — the API is unreachable.
	if ($error->getCode() !== 0) {
		$output["checks"]["metadata-api"]["ok"] = true;
	} else {
		$output["checks"]["metadata-api"]["ok"] = false;
		$output["checks"]["metadata-api"]["debug"] = $error->getMessage();
	}
}
echo json_encode($output);
