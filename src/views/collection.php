<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - <?=htmlspecialchars($data['name'])?> Collection</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<link rel="manifest" href="/manifest.json" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script type="text/javascript">
			const mediaManager = "<?=htmlspecialchars(getenv('MEDIA_MANAGER_URL'))?>";
			const mediaManager_apiKey = "<?=htmlspecialchars(getenv('KEY_LUCOS_MEDIA_MANAGER'))?>";
		</script>
	</head>
	<body>
		<lucos-navbar bg-colour="#000020">Metadata Manager - <?=htmlspecialchars($data['name'])?> Collection</lucos-navbar>
		<a href="/collections" class="mock-button nav-home">&lt;- All Collections </a>
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

			<?php if (!empty($data["slug"])) { ?>
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
				<div class="pagination">
				<?php
				$nextpage = $page + 1;
				$prevpage = $page - 1;
				if ($prevpage > 0) {
					?><a href="<?=htmlspecialchars("/collections/".urlencode($data["slug"])."?page={$prevpage}")?>">&lt;- Prev</a> | <?php
				}
				?>
				Page <?=$page?> out of <?=$totalPages?>
				<?php if ($page < $totalPages) {
				?>
					| <a href="<?=htmlspecialchars("/collections/".urlencode($data["slug"])."?page={$nextpage}")?>">Next -&gt;</a>
				<?php
				}
				?>
				</div>
				<collection-controls slug="<?=htmlspecialchars(urlencode($data["slug"]))?>"></collection-controls>
				<form method="post" action="/collections/<?=htmlspecialchars(urlencode($data["slug"]))?>/delete" data-confirm="Are you sure you want to delete collection <?=htmlspecialchars($data['name'])?>?">
					<input type="submit" value="Delete Whole Collection" class="standalone danger" />
				</form>
			<?php } ?>
		</div>
		<script src="/script.js" type="text/javascript"></script>
</body>
</html>