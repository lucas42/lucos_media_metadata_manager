<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - track <?=$trackid?></title>
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
		<lucos-navbar bg-colour="#000020">Metadata Manager - track <?=$trackid?></lucos-navbar>
		<a href="/" class="mock-button nav-home">&lt;- Home </a>
		<div id="content" class="trackpage">
<form method="post" id="trackform">
	<?php echo csrfTokenField(); ?>
	<header>
		<input type="submit" value="Save" class="primary-submit" />
		<h2>Metadata</h2>
	</header>
<?php foreach ($form_fields as $key => $field) {
	// Pass V3 tag arrays directly — field.php handles rendering per field type
	$values = $data["tags"][$key] ?? null;
?>
	<div class="form-field">
<?php
	include 'field.php';

	// Show search link for simple single-value tags
	$searchValue = (!empty($values) && count($values) === 1) ? ($values[0]["name"] ?? null) : null;
	if (!is_null($searchValue)) {?>
		<a href="/search?p.<?=htmlspecialchars($key)?>=<?=htmlspecialchars(urlencode($searchValue))?>" class='predicate-search' target="_blank" title='Find all tracks with <?=htmlspecialchars($key)?> "<?=htmlspecialchars($searchValue)?>"'>🔍</a>
	<?php } else { ?>
		<span class='predicate-search disabled'>🔍</span>
	<?php } ?>
	<?php if (!empty($field["eolas_add_url"])) { ?>
		<a href="<?=htmlspecialchars($field["eolas_add_url"])?>" class='eolas-add' target="_blank" title='Add a new <?=htmlspecialchars($key)?> in eolas'>➕</a>
	<?php } ?>
	</div>
<?php
}
?>
	<footer><input type="submit" value="Save" class="primary-submit" /></footer>
</form>
<h2>Additional Details</h2>
<div id="details">
<?php
	$unknown_tag_keys = array_diff_key($data["tags"], $form_fields);
	foreach ($unknown_tag_keys as $key => $values) {
		$displayValues = array_map(function($v) { return $v["name"] ?? $v["uri"] ?? ""; }, $values);
		$val = implode(", ", array_filter($displayValues));
?>
	<div class="detail">
		<span class="key"><?=htmlspecialchars(str_replace('_', ' ', $key))?></span>
		<span class="value"><?=htmlspecialchars($val)?></span>
	</div>
<?php
}
?>
	<div class="detail">
		<span class="key">URL</span>
		<span class="value">
			<a href="<?=htmlspecialchars($data["url"])?>" target="_blank">
				<?=htmlspecialchars($data["url"])?>
			</a>
		</span>
	</div>
	<div class="detail">
		<span class="key">Weighting</span>
		<span class="value"><?=$data["weighting"]?></span>
	</div>
	<div class="detail">
		<span class="key">Duration</span>
		<span class="value"><?=$data["duration"]?> seconds</span>
	</div>
	<queue-controls
		data-trackurl="<?=htmlspecialchars($data["url"])?>"
		data-trackid="<?=$data["id"]?>" />
	</div>
	<form method="post" action="/tracks/<?=$data["id"]?>/delete" data-confirm="Are you sure you want to delete track <?=$trackid?>?">
		<?php echo csrfTokenField(); ?>
		<input type="submit" value="Delete Track" class="standalone danger" />
	</form>
</div>
<script src="/script.js" type="text/javascript"></script>
</body>
</html>
