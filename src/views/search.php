<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - Search Results</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/track-ui.js"></script>
	</head>
	<body>
		<div id="lucos_navbar">
			<a href="https://l42.eu/"><img src="https://l42.eu/logo.png" alt="lucOS" id="lucos_navbar_icon" /></a>
			<span id="lucos_navbar_title">Metadata Manager - Search Results</span>
		</div>
		<div id="content">
<h2>Tracks</h2>
<ul>
<?php foreach ($data["tracks"] as $track) {
	$title = $track["tags"]["title"];
	if (empty($title)) $title = $track["url"]; // TODO: just get the file name (excluding extension)
	if (!empty($track["tags"]["artist"])) {
		$title = $track["tags"]["artist"]." - ".$title;
	}
	?><li>
		<a href="/tracks/<?=$track["trackid"]?>">
			<?=htmlspecialchars($title)?>
		</a>
	</li><?php
}?>
</div>
</body>
</html>