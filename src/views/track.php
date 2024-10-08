<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - track <?=$trackid?></title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/form-ui.js"></script>
		<script src="/queue-controls.js"></script>
		<script type="text/javascript">
			const mediaManager = "<?=htmlspecialchars(getenv('MEDIA_MANAGER_URL'))?>";
			const mediaManager_apiKey = "<?=htmlspecialchars(getenv('KEY_LUCOS_MEDIA_MANAGER'))?>";
		</script>
	</head>
	<body>
		<lucos-navbar bg-colour="#000020"><a href="/" id="lucos_navbar_title">Metadata Manager - track <?=$trackid?></a></lucos-navbar>
		<div id="content" class="trackpage">
<form method="post" id="trackform">
	<header>
		<input type="submit" value="Save" class="primary-submit" />
		<h2>Metadata</h2>
	</header>
<?php foreach ($form_fields as $key => $field) {
	if (array_key_exists($key, $data["tags"])) {
		$value = $data["tags"][$key];
	} else {
		$value = null;
	}
?>
	<div class="form-field">
<?php
	include 'field.php';

	if (!is_null($value) and !is_array($value)) {?>
		<a href="/search?p.<?=htmlspecialchars($key)?>=<?=htmlspecialchars(urlencode($value))?>" class='predicate-search' target="_blank" title='Find all tracks with <?=htmlspecialchars($key)?> "<?=htmlspecialchars($value)?>"'>🔍</a>
	<?php } else { ?>
		<span class='predicate-search disabled'>🔍</span>
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
	foreach ($unknown_tag_keys as $key => $val) {
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
		data-trackid="<?=$data["trackid"]?>" />
	</div>
	<form method="post" action="/tracks/<?=$data["trackid"]?>/delete" data-confirm="Are you sure you want to delete track <?=$trackid?>?">
		<input type="submit" value="Delete Track" class="standalone danger" />
	</form>
</div>
<script src="/lucos_navbar.js" type="text/javascript"></script>
</body>
</html>