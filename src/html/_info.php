<?php
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

	// TODO: don't hardcode this URL, use same fetch logic as elsewhere.
	$tracks = file_get_contents("https://media-api.l42.eu/v2/tracks");
	if ($tracks === false) {
		$output["checks"]["metadata-api"]["debug"] = error_get_last()["message"];
		$output["checks"]["metadata-api"]["ok"] = false;
	} else {
		$output["checks"]["metadata-api"]["ok"] = true;
	}
	echo json_encode($output);
