<?php
/**
 * @var array|null $target           The album to keep (null in phase 1)
 * @var array      $sources          Source album records (albums to delete)
 * @var int[]      $sourceIds        Source album IDs from the URL
 * @var array      $searchResults    Albums matching the current search query
 * @var array      $sourceTrackCounts  Map of source album id => track count (phase 3 only)
 * @var string     $searchQuery      Current target search query
 * @var string     $sourceQuery      Current source search query
 * @var bool       $confirm          Whether we're on the confirmation step
 * @var string|null $fetchError      Error from the search API call, if any
 */
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - Merge Albums</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<link rel="manifest" href="/manifest.json" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
	</head>
	<body>
		<lucos-navbar bg-colour="#000020">Metadata Manager - Merge Albums</lucos-navbar>
		<a href="/albums" class="mock-button nav-home">&lt;- All Albums</a>
		<div id="content">
			<h1>Merge Albums</h1>

<?php if ($target === null): ?>
			<?php /* ── Phase 1: select target ── */ ?>
			<p>Search for the album to keep. The target album survives the merge; all others are deleted.</p>

			<form method="get" action="/albums/merge">
				<div class="form-field">
					<label for="q">Search albums</label>
					<input type="text" id="q" name="q" value="<?=htmlspecialchars($searchQuery)?>" autofocus>
				</div>
				<input type="submit" value="Search" class="primary-submit">
			</form>

<?php if ($fetchError !== null): ?>
			<p class="error"><?=htmlspecialchars($fetchError)?></p>
<?php elseif ($searchQuery !== "" && empty($searchResults)): ?>
			<p>No albums found for &#8220;<?=htmlspecialchars($searchQuery)?>&#8221;.</p>
<?php elseif (!empty($searchResults)): ?>
			<p>Select the album to keep:</p>
			<ul id="results">
<?php foreach ($searchResults as $album): ?>
				<li>
					<a href="/albums/merge?<?=htmlspecialchars(http_build_query(["targetId" => $album["id"]]))?>">
						<?=htmlspecialchars($album["name"])?>
					</a>
				</li>
<?php endforeach; ?>
			</ul>
<?php endif; ?>

<?php elseif ($confirm): ?>
			<?php /* ── Phase 3: confirmation ── */ ?>
			<p>
				Keeping: <strong><a href="/albums/<?=(int)$target["id"]?>"><?=htmlspecialchars($target["name"])?></a></strong>
			</p>

			<div class="merge-warning">
<?php
	$knownTotal = 0;
	$allCountsKnown = true;
	foreach ($sources as $source) {
		$count = $sourceTrackCounts[$source["id"]] ?? null;
		if ($count === null) { $allCountsKnown = false; } else { $knownTotal += $count; }
	}
	$albumWord = count($sources) === 1 ? "album" : "albums";
?>
<?php if ($allCountsKnown && $knownTotal > 0): ?>
				<p><?=$knownTotal?> <?=$knownTotal === 1 ? "track" : "tracks"?> will be moved to <strong><?=htmlspecialchars($target["name"])?></strong>. The following <?=$albumWord?> will be deleted. This cannot be undone.</p>
<?php else: ?>
				<p>The following <?=$albumWord?> will be merged into <strong><?=htmlspecialchars($target["name"])?></strong> and deleted. This cannot be undone.</p>
<?php endif; ?>
				<ul>
<?php foreach ($sources as $source):
	$count = $sourceTrackCounts[$source["id"]] ?? null;
?>
					<li>
						<?=htmlspecialchars($source["name"])?>
<?php if ($count !== null): ?>
						(<?=$count?> <?=$count === 1 ? "track" : "tracks"?>)
<?php endif; ?>
					</li>
<?php endforeach; ?>
				</ul>
			</div>

<?php
	$backParams = ["targetId" => $target["id"], "sourceIds" => $sourceIds];
	if ($sourceQuery !== "") $backParams["sq"] = $sourceQuery;
?>
			<a href="/albums/merge?<?=htmlspecialchars(http_build_query($backParams))?>" class="mock-button">
				&lt;- Back
			</a>

			<form method="post" action="/albums/merge">
				<?=csrfTokenField()?>
				<input type="hidden" name="targetId" value="<?=(int)$target["id"]?>">
<?php foreach ($sourceIds as $sid): ?>
				<input type="hidden" name="sourceIds[]" value="<?=(int)$sid?>">
<?php endforeach; ?>
				<input type="submit" value="Confirm merge" class="standalone danger">
			</form>

<?php else: ?>
			<?php /* ── Phase 2: select sources ── */ ?>
			<p>
				Keeping: <strong><a href="/albums/<?=(int)$target["id"]?>"><?=htmlspecialchars($target["name"])?></a></strong>
				&mdash; <a href="/albums/merge<?=$searchQuery !== "" ? "?" . htmlspecialchars(http_build_query(["q" => $searchQuery])) : ""?>">change</a>
			</p>

			<h2>Select albums to merge in</h2>
			<p>These albums will be deleted and their tracks moved to <strong><?=htmlspecialchars($target["name"])?></strong>.</p>

			<form method="get" action="/albums/merge">
				<input type="hidden" name="targetId" value="<?=(int)$target["id"]?>">
<?php foreach ($sourceIds as $sid): ?>
				<input type="hidden" name="sourceIds[]" value="<?=(int)$sid?>">
<?php endforeach; ?>
				<div class="form-field">
					<label for="sq">Search albums to merge in</label>
					<input type="text" id="sq" name="sq" value="<?=htmlspecialchars($sourceQuery)?>" autofocus>
				</div>
				<input type="submit" value="Search" class="primary-submit">
			</form>

<?php if ($fetchError !== null): ?>
			<p class="error"><?=htmlspecialchars($fetchError)?></p>
<?php elseif ($sourceQuery !== "" && empty($searchResults)): ?>
			<p>No albums found for &#8220;<?=htmlspecialchars($sourceQuery)?>&#8221;.</p>
<?php elseif (!empty($searchResults)): ?>
			<ul id="results">
<?php foreach ($searchResults as $album):
	$addIds    = array_merge($sourceIds, [(int)$album["id"]]);
	$addParams = ["targetId" => $target["id"], "sourceIds" => $addIds];
	if ($sourceQuery !== "") $addParams["sq"] = $sourceQuery;
?>
				<li>
					<a href="/albums/merge?<?=htmlspecialchars(http_build_query($addParams))?>">
						+ <?=htmlspecialchars($album["name"])?>
					</a>
				</li>
<?php endforeach; ?>
			</ul>
<?php endif; ?>

<?php if (!empty($sources)): ?>
			<h2>Albums to merge in</h2>
			<ul id="merge-sources">
<?php foreach ($sources as $source):
	$removeIds    = array_values(array_filter($sourceIds, fn($id) => $id !== (int)$source["id"]));
	$removeParams = ["targetId" => $target["id"], "sourceIds" => $removeIds];
	if ($sourceQuery !== "") $removeParams["sq"] = $sourceQuery;
?>
				<li>
					<?=htmlspecialchars($source["name"])?>
					&mdash;
					<a href="/albums/merge?<?=htmlspecialchars(http_build_query($removeParams))?>">Remove</a>
				</li>
<?php endforeach; ?>
			</ul>

<?php
	$confirmParams = ["targetId" => $target["id"], "sourceIds" => $sourceIds, "confirm" => "1"];
?>
			<a href="/albums/merge?<?=htmlspecialchars(http_build_query($confirmParams))?>" class="mock-button primary-submit">
				Review merge &rarr;
			</a>
<?php endif; ?>

<?php endif; ?>
		</div>
		<script src="/script.js" type="text/javascript"></script>
	</body>
</html>
