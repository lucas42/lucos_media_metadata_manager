<?php

/**
 * Sets a given http status page and displays an error page
 */
function displayError($statusCode, $errorMessage, $trackid=null) {
	http_response_code($statusCode);
	require("../views/error.php");
}