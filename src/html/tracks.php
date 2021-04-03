<?php
require("../controllers/updatetrack.php");
require("../controllers/viewtrack.php");
require("../controllers/randomtrack.php");
require("../controllers/queuetrack.php");
require_once("../controllers/error.php");

$urlparts = explode('/', $_SERVER['PHP_SELF']);
$trackid = (count($urlparts) > 2) ? $urlparts[2] : null;

if (array_key_exists("trackid", $_GET)) {
	header("Location: /tracks/${_GET["trackid"]}");
} elseif (array_key_exists("random", $_POST)) {
	pickRandomTrack();
} elseif ($trackid == "queue") {
	if (array_key_exists("url", $_POST)) {
		queueTrack($_POST["url"], $_POST["pos"]);
	} else {
		displayError(400, "Need to POST a url to queue");
	}
} elseif (!is_numeric($trackid)) {
	displayError(404, "Need to provide a numerical trackid in URL", $trackid);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	updateTrack($trackid, $_POST);
} else {
	viewTrack($trackid);
}
