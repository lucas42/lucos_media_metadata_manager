<?php
require("../controllers/randomtrack.php");

if (array_key_exists("random", $_POST)) {
	pickRandomTrack();
} else if (array_key_exists("trackid", $_GET)) {
	header("Location: /tracks/${_GET["trackid"]}");
} else {
	require("../views/home.php");
}
