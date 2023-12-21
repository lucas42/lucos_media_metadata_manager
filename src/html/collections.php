<?php
require("../authentication.php");
require("../controllers/updatecollection.php");
require("../controllers/viewcollection.php");
require("../controllers/deletecollection.php");
require("../controllers/listcollections.php");
require_once("../controllers/error.php");


$urlparts = explode('/', $_SERVER['PHP_SELF']);
$slug = (count($urlparts) > 2) ? $urlparts[2] : null;
$subpath = (count($urlparts) > 3) ? $urlparts[3] : null;

if (!$subpath) {
	if (array_key_exists("slug", $_GET)) {
		header("Location: /collections/${_GET["slug"]}");
	} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
		updateCollection($slug, $_POST);
	} elseif ($slug == "new") {
		newCollectionForm();
	} elseif ($slug) {
		viewCollection($slug);
	} else {
		listCollections();
	}
} elseif ($subpath == "delete") {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		deleteCollection($slug);
	} else {
		header("Allow: POST");
		displayError(405, "Delete endpoint needs a POST request");
	}
} else {
	displayError(404, "Collection subpath $subpath not recognised");
}