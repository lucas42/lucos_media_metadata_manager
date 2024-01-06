<?php
require("../authentication.php");
require("../controllers/searchtracks.php");
require("../controllers/bulkupdatetracks.php");
require_once("../controllers/error.php");

$params = array();
$page = empty($_GET['page']) ? null : $_GET['page'];
if (!is_numeric($page) or $page < 1) $page = "1";

// If there's a search query, that takes precendence
if (array_key_exists('q', $_GET)) {
	$params['q'] = $_GET['q'];

// Without a search query, look for all the non-empty predicates (keys start with 'p.') and use them to search
} else {
	foreach ($_GET as $key => $val) {

		// HACK: PHP does an annoying thing of changing dots & spaces to underscores in GET params, replace them back
		$key = str_replace('p_', 'p.', $key);
		if (!str_starts_with($key, 'p.')) continue;
		if (str_ends_with($key, '_null')) continue;
		$key = str_replace('_', ' ', $key);
		if (is_array($val)) $val = implode(",", $val);
		if (is_null($val) or $val === "") continue;

		$params[$key] = $val;
	}
}
try {
	if (!empty($params)) {
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			bulkUpdateTracks($params, $page, $_POST);
		} else {
			searchTracks($params, $page);
		}
	} else {
		searchHomepage();
	}
} catch (Exception $error) {
	displayError(500, $error->getMessage());
}