<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - <?=htmlspecialchars($data['name'])?> Collection</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/form-ui.js"></script>
		<script src="/queue-controls.js"></script>
	</head>
	<body>
		<lucos-navbar bg-colour="#000020"><a href="/" id="lucos_navbar_title">Metadata Manager - <?=htmlspecialchars($data['name'])?> Collection</a></lucos-navbar>
		<div id="content">
			<h2>Collection Metadata</h2>
			<form method="post" id="collectionform">
				<div class="form-field">
				<?php $key = "slug"; $value = $data["slug"]; $field = ["type" => "text", "hint" => "Unique string to use for this collection in URLs etc."]; $disabled = !empty($data["slug"]);
					include 'field.php';
				?>
				</div>
				<div class="form-field">
				<?php $key = "name"; $value = $data["name"]; $field = ["type" => "text", "hint" => "Unique human-readable name for this collection"]; $disabled = false;
					include 'field.php';
				?>
				</div>
				<input type="submit" value="Save" class="primary-submit" />
			</form>

			<h2>Tracks</h2>
			<ul id="results">
			<?php foreach ($tracks as $track) {
				?><li>
					<h3><a href="/tracks/<?=$track["id"]?>"><?=htmlspecialchars($track["title"])?></a></h3>
					<queue-controls
						data-trackurl="<?=htmlspecialchars($track["url"])?>"
						data-trackid="<?=$track["id"]?>" />
				</li><?php
			}
			?>
			</ul>
			<form method="post" action="/collections/<?=htmlspecialchars(urlencode($data["slug"]))?>/delete">
				<input type="submit" value="Delete Whole Collection" class="standalone danger" />
			</form>

		</div>
		<script src="/lucos_navbar.js" type="text/javascript"></script>
</body>
</html>