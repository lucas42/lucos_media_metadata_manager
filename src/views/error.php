<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - Error</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<link rel="manifest" href="/manifest.json" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/form-ui.js"></script>
	</head>
	<body>
		<lucos-navbar bg-colour="#000020">Metadata Manager</lucos-navbar>
		<a href="/" class="mock-button nav-home">&lt;- Home </a>
		<div id="content">
			<h1>An error occured</h1>
			<pre style="white-space: break-spaces;"><?=htmlspecialchars($errorMessage)?></pre>
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
		<script src="/lucos_navbar.js" type="text/javascript"></script>
	</body>
</html>