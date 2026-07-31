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
 * A shutdown function *does* work: `output_buffering = 4096` (the stock
 * php.ini-production, unoverridden here) means headers aren't actually sent
 * until either the buffer fills or the request ends, so at the point a fatal
 * fires `headers_sent()` is still false even if a view had already echoed
 * some markup (nav/header content, typically, before reaching whatever
 * fatals). That buffered partial output has to be discarded before the
 * friendly page is written, or the response ends up as the partial page and
 * the friendly page concatenated together.
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
			// Discard whatever partial markup the crashed request already
			// echoed into the output buffer — otherwise it's still sitting
			// there and renderFatalErrorPage() would just append after it.
			while (ob_get_level() > 0) {
				ob_end_clean();
			}
			http_response_code(500);
			renderFatalErrorPage();
		}
	}
}

register_shutdown_function('handleFatalErrorShutdown');
