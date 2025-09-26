<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<link rel="manifest" href="/manifest.json" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
	</head>
	<body>
		<lucos-navbar bg-colour="#000020">Metadata Manager</lucos-navbar>
		<a href="/" class="mock-button nav-home">&lt;- Home </a>
		<div id="content">
			<h2 id="basic">Basic Search</h2>
			<small>Attempts to find term across any tags on the track, including substrings.  Case insensitive.</small>
			<form method="get" action="/search">
				<div class="form-field">
					<label for="basic-input" class="key-label medium-key">Search Term</label>
					<span class="form-input">
						<input type="text" name="q" id="basic-input" autofocus/>
					</span>
					<input type="submit" value="Search" />
				</div>
			</form>

			<h2 id="advanced">Advanced Search</h2>
			<small>Exact matches only.  Case sensitive.  Needs to match all fields (excluding those left blank).</small>
			<form method="get" action="/search">
			<?php foreach ($form_fields as $key => $field) {
				$key = 'p.'.$key;
				$value = null;
			?>
				<div class="form-field">
				<?php
					include 'field.php';
				?>
				</div>
			<?php } ?>
				<input type="submit" value="Search" class="primary-submit" />
			</form>
		</div>
		<script src="/script.js" type="text/javascript"></script>
	</body>
</html>