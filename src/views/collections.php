<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - Collections</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
	</head>
	<body>
		<lucos-navbar bg-colour="#000020"><a href="/" id="lucos_navbar_title">Metadata Manager - Collections</a></lucos-navbar>
		<div id="content">
			<form method="get" action="/collections/new">
				<input type="submit" value="Create New Collection" class="standalone" />
			</form>
			<h2>All Collections</h2>
			<ul id="results">
			<?php foreach ($collections as $collection) {
				?><li>
					<h3><a href="/collections/<?=htmlspecialchars($collection["slug"])?>"><?=htmlspecialchars($collection["name"])?> [<?=count($collection["tracks"])?> Tracks]</a></h3>
				</li><?php
			}
			?>
			</ul>
		</div>
		<script src="/lucos_navbar.js" type="text/javascript"></script>
</body>
</html>