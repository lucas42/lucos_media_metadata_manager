<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - track <?=$trackid?></title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/track-ui.js"></script>
	</head>
	<body>
		<div id="lucos_navbar">
			<a href="https://l42.eu/"><img src="https://l42.eu/logo.png" alt="lucOS" id="lucos_navbar_icon" /></a>
			<span id="lucos_navbar_title">Metadata Manager - track <?=$trackid?></span>
		</div>
		<div id="content">
<h2>Metadata</h2>
<form method="post" id="trackform">
<?php foreach ($form_fields as $key => $field) {
	if (array_key_exists($key, $data["tags"])) {
		$value = $data["tags"][$key];
		$is_null = false;
	} else {
		$value = null;
		$is_null = true;
	}?>
	<div class="form-field">
		<label for="<?=htmlspecialchars($key)?>" class="key-label">
			<?=htmlspecialchars(str_replace('_', ' ', $key))?>
		</label>
		<span class="form-input">
		<?php switch($field["type"]) {
			case "text":
				?>
				<input 
					type="text" 
					id="<?=htmlspecialchars($key)?>"
					name="<?=htmlspecialchars($key)?>"
					value="<?=htmlspecialchars($value)?>" />
				<?php
				break;
			case "range":
				?>
				<input 
					type="range" 
					id="<?=htmlspecialchars($key)?>"
					name="<?=htmlspecialchars($key)?>"
					value="<?=htmlspecialchars($value)?>"
					min="0"
					max="10"
					step="0.1" />
				<span class="preview" ></span>
				<?php
				break;
			case "select":
				?>
				<select
					id="<?=htmlspecialchars($key)?>"
					name="<?=htmlspecialchars($key)?>">
					<option></option>
					<?php foreach ($field["values"] as $option) {?>
					<option <?=($option === $value)?"selected":""?>>
						<?=$option?>
					</option>
					<?php
					}?>
				</select>
				<?php
				break;
			default:
				?>Unknown type "<?=$field["type"]?>"<?php
		}?>
		</span>
		<span class="isnull">
			<input
				type="checkbox"
				id="<?=htmlspecialchars($key)?>_null"
				name="<?=htmlspecialchars($key)?>_null"
				<?=$is_null ? "checked" : ""?>
				>
			<label for="<?=htmlspecialchars($key)?>_null">Null</label>
		</span>
	</div>
<?php
}
?>
	<input type="submit" value="Save" id="save" />
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
</div>
</body>
</html>