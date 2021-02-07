<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - Error</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/track-ui.js"></script>
	</head>
	<body>
		<div id="lucos_navbar">
			<a href="https://l42.eu/"><img src="https://l42.eu/logo.png" alt="lucOS" id="lucos_navbar_icon" /></a>
			<span id="lucos_navbar_title">Metadata Manager - Error</span>
		</div>
		<div id="content">
			<h1><?=htmlspecialchars($errorMessage)?></h1>
			<h2>Try Another Track?</h2>
			<form method="get" action="/tracks">
				<div class="form-field">
					<label for="trackid" class="key-label">Track ID</label>
					<span class="form-input">
						<input type="text" name="trackid" value="<?=htmlspecialchars($trackid)?>"/>
					</span>
					<input type="submit" value="View Track" />
				</div>
			</form>
			<form method="post" action="/tracks">
				<input type="hidden" name="random" value="true"/>
				<input type="submit" value="View Random Track" />
			</form>
		</div>
</body>
</html>