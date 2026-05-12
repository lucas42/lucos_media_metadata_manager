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
			"failThreshold" => 3,
		]
	],
	"metrics" => (object)[],
];
try {
	fetchFromApi("/v3/tracks/1", timeout: 0.5);
	$output["checks"]["metadata-api"]["ok"] = true;
} catch (ApiError $error) {
	$debugParts = [$error->getMessage()];
	if ($error->latencyMs !== null) {
		$debugParts[] = "latency: {$error->latencyMs}ms";
	}
	if ($error->resolvedIp !== null) {
		$debugParts[] = "resolved IP: {$error->resolvedIp}";
	}
	if ($error->responseBody !== null) {
		$debugParts[] = "response body: " . substr($error->responseBody, 0, 200);
	}
	$debugStr = implode(", ", $debugParts);
	error_log("metadata-api probe failed: {$debugStr}");
	$output["checks"]["metadata-api"]["ok"] = false;
	$output["checks"]["metadata-api"]["debug"] = $debugStr;
}
echo json_encode($output);
