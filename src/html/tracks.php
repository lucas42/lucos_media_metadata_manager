<?php

require("../conneg.php");

$urlparts = explode('/', $_SERVER['PHP_SELF']);
$trackid = (count($urlparts) > 2) ? $urlparts[2] : null;
$subpath = (count($urlparts) > 3) ? $urlparts[3] : null;

if (is_numeric($trackid) and choose_rdf_over_html()) {
	header("Location: " . getenv("MEDIA_API") . "/v2/tracks/{$trackid}");
	exit;
}

require("../authentication.php");
require("../controllers/updatetrack.php");
require("../controllers/viewtrack.php");
require("../controllers/deletetrack.php");
require("../controllers/randomtrack.php");
require_once("../controllers/error.php");


if (array_key_exists("trackid", $_GET)) {
	header("Location: /tracks/{$_GET["trackid"]}");
} elseif (array_key_exists("random", $_POST)) {
	pickRandomTrack();
} elseif (!is_numeric($trackid)) {
	displayError(404, "Need to provide a numerical trackid in URL", $trackid);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!$subpath) {
		updateTrack($trackid, $_POST);
	} elseif ($subpath === "delete") {
		deleteTrack($trackid);
	} else {
		displayError(404, "Track subpath $subpath not recognised");
	}
} else {
	viewTrack($trackid);
}
