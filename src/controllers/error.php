<?php

if (!function_exists('displayError')) {
	/**
	 * Sets a given http status code and displays an error page.
	 *
	 * @param int         $statusCode   HTTP status to send.
	 * @param string      $errorMessage Human-readable message shown in a <p>.
	 * @param string|null $trackid      Optional track ID to pre-fill the "Try another track" form.
	 * @param string|null $errorDetail  Optional technical detail (e.g. API reason text) shown in <pre>.
	 */
	function displayError($statusCode, $errorMessage, $trackid=null, $errorDetail=null) {
		http_response_code($statusCode);
		require("../views/error.php");
	}
}

if (!function_exists('displayApiError')) {
	/**
	 * Displays an error page for an unexpected API error, mapping the upstream
	 * status to the correct manager status (500 for unexpected 4xx, 502 for 5xx
	 * and network failures) and using the two class-based message templates.
	 * Intentional per-code special-cases (404, 409, etc.) are handled by callers
	 * before reaching this.
	 */
	function displayApiError(ApiError $error, string $context, $trackid=null) {
		displayError($error->managerStatus(), $context . "\n\n" . $error->userMessage(), $trackid, $error->detail());
	}
}