<?php
/**
 * @var array       $album        The album record from the API: {id, name, uri}
 * @var array       $tracks       Summarised tracks tagged with this album (may be empty)
 * @var int         $currentPage  Current page number for the track list
 * @var int         $totalPages   Total pages for the track list
 * @var string|null $tracksError  Optional error message if the track fetch failed
 */
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - <?=htmlspecialchars($album["name"])?></title>
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
		<lucos-navbar bg-colour="#000020">Metadata Manager - <?=htmlspecialchars($album["name"])?></lucos-navbar>
		<a href="/albums" class="mock-button nav-home">&lt;- All Albums </a>
		<div id="content">
			<h1><?=htmlspecialchars($album["name"])?></h1>

			<h2>Metadata</h2>
			<form method="post" id="albumform">
				<?php echo csrfTokenField(); ?>
				<div class="form-field">
					<?php
					$key = "name";
					$values = [["name" => $album["name"]]];
					$field = ["type" => "text", "hint" => "Human-readable name for this album"];
					$disabled = false;
					include 'field.php';
					?>
				</div>
				<input type="submit" value="Save" class="primary-submit" />
			</form>

			<h2>Tracks</h2>
<?php if (!empty($tracksError)) { ?>
			<p>Couldn't load tracks for this album: <?=htmlspecialchars($tracksError)?></p>
<?php } else if (empty($tracks)) { ?>
			<p>No tracks in this album yet.</p>
<?php } else { ?>
			<ul id="results">
<?php foreach ($tracks as $track) { ?>
				<li>
					<h3><a href="/tracks/<?=$track["id"]?>"><?=htmlspecialchars($track["title"])?></a></h3>
					<queue-controls data-trackurl="<?=htmlspecialchars($track["url"])?>" data-trackid="<?=$track["id"]?>" />
				</li>
<?php } ?>
			</ul>
			<div class="pagination">
<?php
	$nextpage = $currentPage + 1;
	$prevpage = $currentPage - 1;
	if ($prevpage > 0) {
?>				<a href="/albums/<?=htmlspecialchars(urlencode((string)$album["id"]))?>?page=<?=$prevpage?>">&lt;- Prev</a> |
<?php } ?>
				Page <?=$currentPage?> out of <?=$totalPages?>
<?php if ($currentPage < $totalPages) { ?>
				| <a href="/albums/<?=htmlspecialchars(urlencode((string)$album["id"]))?>?page=<?=$nextpage?>">Next -&gt;</a>
<?php } ?>
			</div>
<?php } ?>

<?php if (empty($tracks) && empty($tracksError)) { ?>
			<form method="post" action="/albums/<?=htmlspecialchars(urlencode((string)$album["id"]))?>/delete" data-confirm="Are you sure you want to delete album <?=htmlspecialchars($album["name"])?>?">
				<?php echo csrfTokenField(); ?>
				<input type="submit" value="Delete Album" class="standalone danger" />
			</form>
<?php } ?>
		</div>
		<script src="/script.js" type="text/javascript"></script>
	</body>
</html>
