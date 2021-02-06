<?php
	$path = $_SERVER['REDIRECT_URL'];
	$trackid = explode('/', $path)[2];
	if (!is_numeric($trackid)) {
		http_response_code(404);
		echo "Need to provide a numerical trackid in URL";
		exit;
	}
	$form_fields = [
		"title" => "text",
		"artist" => "text",
		"album" => "text",
		"rating" => "range",
	];

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		foreach ($form_fields as $key => $type) {

			// If a tag is marked as null, DELETE it from the API
			// Otherwise, update the value using PUT
			$method = array_key_exists("${key}_null", $_POST) ? "DELETE" : "PUT";
			$val = $_POST[$key];
			$tagurl = "https://media-api.l42.eu/tags/${trackid}/${key}";
			$context = stream_context_create([
				"http" => [
					"method" => $method,
					"header" => "Content-Type: text/plain",
					"content" => $val,
				],
			]);
			file_get_contents($tagurl, false, $context);
		}
		header("Location: /tracks/${trackid}", true, 303);
		exit;
	}

	$apiurl = "https://media-api.l42.eu/tracks/${trackid}";
	$response = file_get_contents($apiurl);
	if ($response === false) {
		$error = error_get_last()["message"];
		if (str_contains($error, "404 Not Found")) {
			http_response_code(404);
			echo "Track ${trackid} Not Found";
			exit;
		}
		http_response_code(502);
		echo "Can't fetch track from API.\n\n".$error["message"];
		exit;
	}
	$data = json_decode($response, true);
	$tags = $data["tags"];
	$unknown_tag_keys = array_diff_key($tags, $form_fields);
?>
<h1>Track <?=$trackid?></h1>
<h2>Metadata</h2>
<form method="post">
<?php foreach ($form_fields as $key => $type) {
	if (array_key_exists($key, $tags)) {
		$value = $tags[$key];
		$is_null = false;
	} else {
		$value = null;
		$is_null = true;
	}?>
	<div class="form-field">
		<label for="<?=htmlspecialchars($key)?>">
			<?=htmlspecialchars($key)?>
		</label>
		<?php switch($type) {
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
				<?php
				break;
			default:
				?>Unknown type "<?=$type?>"<?php
		}?>
		<input 
			type="checkbox" 
			id="<?=htmlspecialchars($key)?>_null" 
			name="<?=htmlspecialchars($key)?>_null" 
			<?=$is_null ? "checked" : ""?>
			>
		<label for="<?=htmlspecialchars($key)?>_null">Null?</label>
	</div>
<?php
}
?>
	<input type="submit" value="Save"/>
</form>
<h2>Additional Details</h2>
<table>
<?php foreach ($unknown_tag_keys as $key => $val) {?>
	<tr>
		<td class="key"><?=htmlspecialchars($key)?></td>
		<td class="value"><?=htmlspecialchars($val)?></td>
	</tr>
<?php
}
?>
	<tr>
		<td class="key">URL</td>
		<td class="value">
			<a href="<?=htmlspecialchars($data["url"])?>"><?=htmlspecialchars($data["url"])?></a>
		</td>
	</tr>
	<tr>
		<td class="key">Weighting</td>
		<td class="value"><?=$data["weighting"]?></td>
	</tr>
	<tr>
		<td class="key">Duration</td>
		<td class="value"><?=$data["duration"]?> seconds</td>
	</tr>
</table>