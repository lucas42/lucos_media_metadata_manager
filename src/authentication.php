<?php

session_start();

// TODO: consider persisting this across requests to improve performance
$agents = [];

/**
 * Checks whether a given token is authenticated to access the service
 * @return Promise{boolean}
 */
function isAuthenticated($token)
{
	if (empty($token))
		return false;

	// If we've already validated the given token before, approve immediately
	if (!empty($agents[$token]))
		return true;

	// Otherwise, verify it against the authentication service
	$authurl = "https://auth.l42.eu/data?token=" . rawurlencode($token);
	$response = @file_get_contents($authurl);
	if ($response === false) {
		$error = error_get_last()["message"];
		error_log("Authentication Error:" . $error);
		return false;
	}
	else {
		$agents[$token] = json_decode($response, true);
		return true;
	}
}

$token = null;
if (!empty($_GET['token'])) {
	$token = $_GET['token'];
}
elseif (!empty($_COOKIE['auth_token'])) {
	$token = $_COOKIE['auth_token'];
}

if (isAuthenticated($token)) {
	session_regenerate_id(true);
	$secure = (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
	setcookie('auth_token', $token, [
		'httponly' => true,
		'samesite' => 'Strict',
		'secure' => $secure,
		'path' => '/',
	]);
	// Expire any legacy auth_token cookies that may be scoped to specific paths (set before
	// 2026-04-08 when setcookie() was called without an explicit path, defaulting to the
	// request path). A stale path-scoped cookie takes browser precedence over the root-scoped
	// one and causes a redirect loop. Clear the known problem paths explicitly.
	$legacyPaths = ['/tracks/', '/collections/', '/search/'];
	foreach ($legacyPaths as $legacyPath) {
		setcookie('auth_token', '', [
			'expires' => 1,
			'path' => $legacyPath,
			'httponly' => true,
			'samesite' => 'Strict',
			'secure' => $secure,
		]);
	}
}
else {
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
		$protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
	}
	else {
		$protocol = "http";
	}
	http_response_code(302);
	header("Location: https://auth.l42.eu/authenticate?redirect_uri=" . rawurlencode($protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
	exit();
}