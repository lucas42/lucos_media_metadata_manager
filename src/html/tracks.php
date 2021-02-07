<?php
	require("../controllers/updatetrack.php");
	require("../controllers/viewtrack.php");

	$path = $_SERVER['PHP_SELF'];
	$trackid = explode('/', $path)[2];
	if (!is_numeric($trackid)) {
		http_response_code(404);
		echo "Need to provide a numerical trackid in URL";
		exit;
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		updateTrack($trackid, $_POST);
	} else {
		viewTrack($trackid);
	}
