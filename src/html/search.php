<?php

require("../controllers/searchtracks.php");

if (!empty($_GET['q'])) {
	searchTracks($_GET['q'], $_GET['page']);
} else {
	require("../views/home.php"); // TODO: replace with page that just has search box
}