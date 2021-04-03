<?php

/**
 * Adds a track to the media_manager queue
 **/
function queueTrack($url, $position) {
	$queueurl = "https://ceol.l42.eu/queue?url=".urlencode($url)."&pos=".urlencode($position);
	$context = stream_context_create([
		"http" => [
			"method" => "POST",
		],
	]);
	file_get_contents($queueurl, false, $context);
	header("Location: /?queued=true", true, 303);
}