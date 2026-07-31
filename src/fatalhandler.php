<?php
/**
 * Catches uncaught PHP fatals and renders the friendly error page in their
 * place, for cases Apache's `ErrorDocument 500` can't reach.
 *
 * Why this exists rather than relying on ErrorDocument alone: under mod_php,
 * an uncaught fatal is turned into a response by PHP itself before Apache's
 * core gets a chance to apply ErrorDocument substitution — ErrorDocument only
 * intercepts errors Apache's own core generates (missing file, rewrite
 * failure, auth denial), not a status a content-handler module sets
 * internally after it has already started handling the request. Verified
 * locally: a genuine fatal came back as an empty-bodied 500 with
 * `ErrorDocument 500 /500.php` wired up and nothing else (#389).
 *
 * A shutdown function *does* work: at the point a fatal fires, PHP has not
 * yet sent headers (nothing here calls flush() or emits output early), so a
 * shutdown handler can still set the status code and write a body.
 *
 * Wired in globally via `php_admin_value auto_prepend_file` in vhost.conf's
 * <Directory> block, so it applies to every request without editing every
 * entry-point file. Deliberately has no requires beyond errorpage.php, which
 * is itself dependency-free — see that file's docblock.
 */

require_once __DIR__ . '/errorpage.php';

/** Error types PHP cannot recover from — a request that hits one of these ends the script. */
const FATAL_ERROR_TYPES = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

if (!function_exists('isUnrecoverableFatal')) {
	/**
	 * @param array{type:int}|null $error The value from error_get_last().
	 */
	function isUnrecoverableFatal(?array $error): bool {
		return $error !== null && in_array($error['type'], FATAL_ERROR_TYPES, true);
	}
}

if (!function_exists('handleFatalErrorShutdown')) {
	function handleFatalErrorShutdown(): void {
		if (isUnrecoverableFatal(error_get_last()) && !headers_sent()) {
			http_response_code(500);
			renderFatalErrorPage();
		}
	}
}

register_shutdown_function('handleFatalErrorShutdown');
