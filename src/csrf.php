<?php

/**
 * Returns the CSRF token for the current session, generating one if needed.
 */
function csrfToken() {
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['csrf_token'];
}

/**
 * Returns an HTML hidden input containing the CSRF token.
 * Include this inside every form that submits a state-changing POST.
 */
function csrfTokenField() {
	return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

/**
 * Verifies that the CSRF token in the POST body matches the session token.
 * Terminates with a 403 response if the check fails.
 */
function verifyCsrfToken() {
	$sessionToken = $_SESSION['csrf_token'] ?? '';
	$requestToken = $_POST['csrf_token'] ?? '';
	if (empty($sessionToken) || !hash_equals($sessionToken, $requestToken)) {
		http_response_code(403);
		echo "Invalid or missing CSRF token";
		exit();
	}
}
