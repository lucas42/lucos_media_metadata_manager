<?php

require("../controllers/searchtracks.php");

$params = array();
$page = empty($_GET['page']) ? null : $_GET['page'];

// If there's a search query, that takes precendence
if (!empty($_GET['q'])) {
	$params['q'] = $_GET['q'];

// Without a search query, look for all the predicates (keys start with 'p.') and use them to search
} else {
	foreach ($_GET as $key => $val) {

		// HACK: PHP does an annoying thing of changing dots to underscores in GET params, replace them back
		$key = str_replace('_','.',$key);
		if (str_starts_with($key, 'p.')) {
			$params[$key] = $val;
		}
	}
}
if (!empty($params)) {
	searchTracks($params, $page);
} else {
	require("../views/home.php"); // TODO: replace with page that just has search box
}