<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - track <?=$trackid?></title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/track-ui.js"></script>
		<script src="/queue-controls.js"></script>
	</head>
	<body>
		<lucos-navbar bg-colour="#000020"><a href="/" id="lucos_navbar_title">Metadata Manager - track <?=$trackid?></a></lucos-navbar>
		<div id="content">
<form method="post" id="trackform">
	<input type="submit" value="Save" class="primary-submit" />
	<h2>Metadata</h2>
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

	if (!empty($value)) {?>
		<a href="/search?p.<?=htmlspecialchars($key)?>=<?=htmlspecialchars(urlencode($value))?>" class='predicate-search' target="_blank" title='Find all tracks with <?=htmlspecialchars($key)?> "<?=htmlspecialchars($value)?>"'>🔍</a>
	<?php } else { ?>
		<span class='predicate-search disabled'>🔍</span>
	<?php } ?>
	</div>
<?php
}
?>
	<input type="submit" value="Save" class="primary-submit" />
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
</div>
<queue-controls
	data-trackurl="<?=htmlspecialchars($data["url"])?>"
	data-trackid="<?=$data["trackid"]?>" />
</div>
<script src="/lucos_navbar.js" type="text/javascript"></script>
</body>
</html>