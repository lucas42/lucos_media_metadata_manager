<?php
/**
 * @var array $albums       List of album records from the API
 * @var int   $currentPage  Current page number (1-indexed)
 * @var int   $totalPages   Total number of pages available
 */
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - Albums</title>
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
		<lucos-navbar bg-colour="#000020">Metadata Manager - Albums</lucos-navbar>
		<a href="/" class="mock-button nav-home">&lt;- Home </a>
		<div id="content">
			<h1>Albums</h1>
			<a href="/albums/merge" class="mock-button">Merge albums</a>
<?php if (empty($albums)) { ?>
			<p>No albums yet.</p>
<?php } else { ?>
			<ul id="results">
<?php foreach ($albums as $album) { ?>
				<li>
					<h3><a href="/albums/<?=htmlspecialchars(urlencode((string)$album["id"]))?>"><?=htmlspecialchars($album["name"])?></a></h3>
				</li>
<?php } ?>
			</ul>
			<div class="pagination">
<?php
	$nextpage = $currentPage + 1;
	$prevpage = $currentPage - 1;
	if ($prevpage > 0) {
?>				<a href="/albums?page=<?=$prevpage?>">&lt;- Prev</a> |
<?php } ?>
				Page <?=$currentPage?> out of <?=$totalPages?>
<?php if ($currentPage < $totalPages) { ?>
				| <a href="/albums?page=<?=$nextpage?>">Next -&gt;</a>
<?php } ?>
			</div>
<?php } ?>
		</div>
		<script src="/script.js" type="text/javascript"></script>
	</body>
</html>
