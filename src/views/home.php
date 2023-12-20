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
			<h2>Find a track</h2>
			<form method="get" action="/search">
				<div class="form-field">
					<label for="trackid" class="key-label">Search Term</label>
					<span class="form-input">
						<input type="text" name="q"/>
					</span>
					<input type="submit" value="Search" />
				</div>
			</form>
			<form method="get" action="/tracks">
				<div class="form-field">
					<label for="trackid" class="key-label">Track ID</label>
					<span class="form-input">
						<input type="text" name="trackid"/>
					</span>
					<input type="submit" value="View Track" />
				</div>
			</form>
			<form method="post" action="/tracks">
				<input type="hidden" name="random" value="true"/>
				<input type="submit" value="View Random Track" class="standalone" />
			</form>
			<a href="/search#advanced" class="standalone">Advanced Search</a>
		</div>
		<script src="/lucos_navbar.js" type="text/javascript"></script>
	</body>
</html>