<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/track-ui.js"></script>
	</head>
	<body>
		<div id="lucos_navbar">
			<a href="https://l42.eu/"><img src="https://l42.eu/logo.png" alt="lucOS" id="lucos_navbar_icon" /></a>
			<span id="lucos_navbar_title">Metadata Manager</span>
		</div>
		<div id="content">
			<h2>Select a track</h2>
			<form method="get" action="/tracks">
				<div class="form-field">
					<label for="trackid" class="key-label">Track ID</label>
					<span class="form-input">
						<input type="text" name="trackid"/>
					</span>
					<input type="submit" value="View Track" />
				</div>
			</form>
			<form method="get" action="/search">
				<div class="form-field">
					<label for="trackid" class="key-label">Search Term</label>
					<span class="form-input">
						<input type="text" name="q"/>
					</span>
					<input type="submit" value="Search" />
				</div>
			</form>
			<form method="post" action="/tracks">
				<input type="hidden" name="random" value="true"/>
				<input type="submit" value="View Random Track" />
			</form>
		</div>
</body>
</html>