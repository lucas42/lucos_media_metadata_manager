<?php
require("../authentication.php");
require("../controllers/updatecollection.php");
require("../controllers/viewcollection.php");

$urlparts = explode('/', $_SERVER['PHP_SELF']);
$slug = (count($urlparts) > 2) ? $urlparts[2] : null;


if (array_key_exists("slug", $_GET)) {
	header("Location: /collections/${_GET["slug"]}");
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	updateCollection($slug, $_POST);
} elseif ($slug == "new") {
	newCollectionForm();
} elseif ($slug) {
	viewCollection($slug);
} else {
	print("//TODO: list collections");
}
