<?php
/**
 * Defensive fallback for `ErrorDocument 500` (vhost.conf). The path that
 * actually catches a PHP fatal is fatalhandler.php's shutdown handler — see
 * that file for why ErrorDocument alone doesn't reach a fatal under mod_php.
 * This stays wired up for any 500 Apache's own core generates outside PHP
 * execution.
 */

require_once __DIR__ . '/../errorpage.php';

http_response_code(500);
renderFatalErrorPage();
