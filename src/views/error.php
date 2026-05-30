<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - Error</title>
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
			<h1>An error occured</h1>
			<?php
				// Split on the Detail separator so the human-readable message renders as prose
				// and any technical detail (e.g. the API's rejection reason) renders as code.
				$detailSep = "\n\nDetail: ";
				$detailPos = strpos($errorMessage, $detailSep);
				if ($detailPos !== false) {
					$mainMessage = substr($errorMessage, 0, $detailPos);
					$detail = substr($errorMessage, $detailPos + strlen($detailSep));
				} else {
					$mainMessage = $errorMessage;
					$detail = null;
				}
			?>
			<p style="white-space: pre-wrap;"><?=htmlspecialchars($mainMessage)?></p>
			<?php if ($detail !== null): ?>
			<pre>Detail: <?=htmlspecialchars($detail)?></pre>
			<?php endif; ?>
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
		<script src="/script.js" type="text/javascript"></script>
	</body>
</html>