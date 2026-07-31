<?php
/**
 * Renders the friendly "something's gone wrong" page shown for a 500 response.
 *
 * Used from two places: fatalhandler.php's shutdown handler (the path that
 * actually catches a PHP fatal — see that file for why) and html/500.php
 * (a defensive ErrorDocument fallback for any 500 Apache's own core generates
 * outside PHP execution).
 *
 * Deliberately self-contained: no require of authentication.php, api.php, or
 * any src/views/ partial, and no API/database round-trip. Both call sites can
 * be reached when the app's shared bootstrap or a view partial is what's
 * broken, so this file must not depend on either — reusing that code would
 * let the error page join the outage instead of reporting it. (2026-07-31
 * incident: mbstring missing broke every view that renders a form field; see
 * lucas42/lucos_media_metadata_manager#389.)
 *
 * style.css, /icon, manifest.json and /script.js are static files Apache
 * serves directly from DocumentRoot — not app bootstrap — so reusing them for
 * visual consistency with the rest of the site doesn't reintroduce the
 * dependency this file is meant to avoid.
 */

if (!function_exists('renderFatalErrorPage')) {
	function renderFatalErrorPage(): void {
		?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Lucos Media Metadata Manager - Error</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<link rel="manifest" href="/manifest.json" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
	</head>
	<body>
		<lucos-navbar bg-colour="#000020" aithne-origin="<?=htmlspecialchars(getenv('AITHNE_ORIGIN') ?: '')?>">Metadata Manager</lucos-navbar>
		<a href="/" class="mock-button nav-home">&larr; Home</a>
		<div id="content">
			<h1>Something's gone wrong on our side</h1>
			<p>This page failed to load. It's already been recorded in our logs.</p>
			<p>Try again, or go back to the homepage. If you'd like to report it, this may help:</p>
			<p><strong>Time:</strong> <?=htmlspecialchars(gmdate('Y-m-d H:i:s'))?> UTC</p>
		</div>
		<script src="/script.js" type="text/javascript"></script>
	</body>
</html>
<?php
	}
}
