<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - Collections</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<link rel="manifest" href="/manifest.json" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/deletion-success.js"></script>
		<script src="/collection-controls.js"></script>
		<script type="text/javascript">
			const mediaManager = "<?=htmlspecialchars(getenv('MEDIA_MANAGER_URL'))?>";
			const mediaManager_apiKey = "<?=htmlspecialchars(getenv('KEY_LUCOS_MEDIA_MANAGER'))?>";
		</script>
	</head>
	<body>
		<lucos-navbar bg-colour="#000020">Metadata Manager - Collections</lucos-navbar>
		<a href="/" class="mock-button nav-home">&lt;- Home </a>
		<div id="content">
			<form method="get" action="/collections/new">
				<input type="submit" value="Create New Collection" class="standalone" />
			</form>
			<h2>All Collections</h2>
			<ul id="results">
			<?php foreach ($collections as $collection) {
				?><li>
					<h3><a href="/collections/<?=htmlspecialchars(urlencode($collection["slug"]))?>"><?=htmlspecialchars($collection["name"])?> [<?=$collection["totalTracks"]?> Tracks]</a></h3>
					<collection-controls slug="<?=htmlspecialchars(urlencode($collection["slug"]))?>"></collection-controls>
				</li><?php
			}
			?>
			</ul>
			<h2>Non-Collection</h2>
				<collection-controls slug="all"></collection-controls>
		</div>
		<script src="/lucos_navbar.js" type="text/javascript"></script>
</body>
</html>