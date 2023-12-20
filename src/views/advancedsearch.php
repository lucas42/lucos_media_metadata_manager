<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/form-ui.js"></script>
	</head>
	<body>
		<lucos-navbar bg-colour="#000020"><a href="/" id="lucos_navbar_title">Metadata Manager</a></lucos-navbar>
		<div id="content">
			<h2 id="basic">Basic Search</h2>
			<small>Attempts to find term across any tags on the track, including substrings.  Case insensitive.</small>
			<form method="get" action="/search">
				<div class="form-field">
					<label for="trackid" class="key-label medium-key">Search Term</label>
					<span class="form-input">
						<input type="text" name="q"/>
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
		<script src="/lucos_navbar.js" type="text/javascript"></script>
	</body>
</html>