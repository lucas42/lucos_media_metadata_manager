<?php
/**
 * Aithne session authentication for lucos_media_metadata_manager.
 *
 * Verifies the aithne_session JWT cookie using local ES256 JWKS verification.
 * session_start() is called here because the PHP session backs CSRF token
 * storage — auth state is no longer stored in the session.
 *
 * Public interface:
 *   requireScope(string $scope): void  — three-branch gate; call at page top / before writes
 *   hasScope(string $scope): bool       — scope check without gating; use for conditional UI
 *
 * Leaves the existing auth_token legacy cookie cleanup untouched
 * (estate-wide cleanup is step 8 of lucos_aithne#12).
 */

// Composer autoloader: supports deployment (COPY src . → vendor/ at same level)
// and local dev (src/authentication.php → vendor/ one level up).
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    throw new RuntimeException('Composer autoloader not found. Run: composer install');
}

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

// PHP session backs CSRF tokens — keep session_start() even though
// authentication state is now carried by the JWT, not the session.
session_start();

// Module-level config (evaluated once at require-time)
$AITHNE_ORIGIN   = getenv('AITHNE_ORIGIN')   ?: 'https://aithne.l42.eu';
$AITHNE_JWKS_URL = getenv('AITHNE_JWKS_URL') ?: ($AITHNE_ORIGIN . '/.well-known/jwks.json');

/** @var array<string,mixed>|null  Verified JWT payload for this request (null = not authenticated) */
$_auth_payload = null;

/** @var array<string,mixed>|null  Last-known-good raw JWKS (re-used on transient fetch failures) */
$_cached_raw_jwks = null;

// ---------------------------------------------------------------------------
// Internal helpers
// ---------------------------------------------------------------------------

/**
 * Strip C0 controls and DEL from attacker-supplied strings before logging.
 */
function _sanitizeForLog(string $s): string
{
    return preg_replace('/[\x00-\x1f\x7f]/', '', $s);
}

/**
 * Build the login redirect URL, with an open-redirect guard on the `next` param.
 */
function _buildLoginUrl(): string
{
    global $AITHNE_ORIGIN;
    $next   = $_SERVER['REQUEST_URI'] ?? '/';
    $parsed = parse_url($next);
    if (!empty($parsed['scheme']) || !empty($parsed['host'])) {
        $next = '/';
    }
    return rtrim($AITHNE_ORIGIN, '/') . '/auth/login?next=' . rawurlencode($next);
}

/**
 * Fetch and parse JWKS key objects, with last-known-good fallback on failure.
 *
 * Tests may inject pre-parsed Key objects by setting:
 *   $GLOBALS['_test_jwks_keys'] = ['kid' => new \Firebase\JWT\Key(...)];
 *
 * @return array<string, \Firebase\JWT\Key>
 */
function _getJwksKeys(): array
{
    global $AITHNE_JWKS_URL, $_cached_raw_jwks;

    if (isset($GLOBALS['_test_jwks_keys'])) {
        return $GLOBALS['_test_jwks_keys'];
    }

    try {
        $context = stream_context_create(['http' => [
            'timeout'       => 5,
            'ignore_errors' => true,
        ]]);
        $body = @file_get_contents($AITHNE_JWKS_URL, false, $context);
        if ($body === false) {
            throw new RuntimeException(
                'JWKS fetch failed: ' . (error_get_last()['message'] ?? 'unknown error')
            );
        }
        $jwks = json_decode($body, true);
        if (!is_array($jwks) || !array_key_exists('keys', $jwks)) {
            throw new RuntimeException('JWKS response is not a valid JWKS object');
        }
        $_cached_raw_jwks = $jwks;
        return JWK::parseKeySet($jwks, 'ES256');
    } catch (Exception $e) {
        error_log(
            'Warning: JWKS fetch failed: ' . _sanitizeForLog($e->getMessage()) .
            ' — serving last-known-good key set'
        );
        if ($_cached_raw_jwks !== null) {
            return JWK::parseKeySet($_cached_raw_jwks, 'ES256');
        }
        throw $e;
    }
}

/**
 * Verify an aithne_session JWT and return its payload as an associative array,
 * or null on any validation failure (expired, wrong iss, wrong aud, bad sig…).
 *
 * Algorithm is pinned to ES256 via the Key objects — the token header alg is
 * never trusted to select a different algorithm.
 *
 * @return array<string,mixed>|null
 */
function _verifyAithneToken(string $token): ?array
{
    global $AITHNE_ORIGIN;

    // Extract kid for safer logging (before full decode)
    $kid = null;
    $tks = explode('.', $token);
    if (count($tks) === 3) {
        $raw = base64_decode(str_pad(strtr($tks[0], '-_', '+/'), 4 - (strlen($tks[0]) % 4), '='));
        $hdr = json_decode((string)$raw, true);
        $kid = $hdr['kid'] ?? null;
    }

    try {
        $keySet      = _getJwksKeys();
        JWT::$leeway = 30;              // 30-second clock-skew tolerance
        $payload     = JWT::decode($token, $keySet);
        $claims      = (array) $payload;

        // Validate iss matches AITHNE_ORIGIN
        if (($claims['iss'] ?? null) !== $AITHNE_ORIGIN) {
            error_log('Auth: JWT iss mismatch: ' . _sanitizeForLog((string)($claims['iss'] ?? '')));
            return null;
        }

        // Validate aud contains l42.eu
        $aud = $claims['aud'] ?? [];
        if (is_string($aud)) {
            $aud = [$aud];
        }
        if (!in_array('l42.eu', $aud, true)) {
            error_log('Auth: JWT aud does not contain l42.eu');
            return null;
        }

        return $claims;
    } catch (Exception $e) {
        $kidStr = $kid !== null ? (' kid=' . _sanitizeForLog((string)$kid)) : '';
        error_log('Auth: JWT verification failed' . $kidStr . ': ' . _sanitizeForLog($e->getMessage()));
        return null;
    }
}

/**
 * Hookable exit for testing.  In tests, set $GLOBALS['_test_exit_fn'] to a
 * callable (e.g. one that throws an exception) to intercept branches 2 and 3.
 */
function _authExit(): void
{
    if (isset($GLOBALS['_test_exit_fn'])) {
        ($GLOBALS['_test_exit_fn'])();
    }
    exit();
}

// ---------------------------------------------------------------------------
// Module-level: verify cookie once at require-time
// ---------------------------------------------------------------------------

if (isset($_COOKIE['aithne_session'])) {
    $_auth_payload = _verifyAithneToken($_COOKIE['aithne_session']);
}

// ---------------------------------------------------------------------------
// Public interface
// ---------------------------------------------------------------------------

/**
 * Check whether the current principal holds the given scope.
 *
 * In development only, a 'render-ui' scope acts as a universal bypass —
 * this lets the lucos developer agent browse the UI during development.
 */
function hasScope(string $scope): bool
{
    global $_auth_payload;
    if ($_auth_payload === null) {
        return false;
    }
    $scopes = $_auth_payload['scopes'] ?? [];
    if (is_string($scopes)) {
        $scopes = [$scopes];
    }
    if (in_array($scope, $scopes, true)) {
        return true;
    }
    // render-ui bypass: development only
    if (
        getenv('ENVIRONMENT') === 'development' &&
        in_array('render-ui', $scopes, true)
    ) {
        return true;
    }
    return false;
}

/**
 * Three-branch scope gate.
 *
 * Branch 1: valid JWT + required scope  → returns (caller proceeds)
 * Branch 2: valid JWT + missing scope   → 403 styled error page
 * Branch 3: no valid JWT                → 302 redirect to aithne login
 *
 * Call at the top of each HTML entry point for the minimum scope required,
 * and again immediately before write operations for media-metadata:write.
 * The call is idempotent for branch 1 (returns instantly when already authorised).
 */
function requireScope(string $scope): void
{
    global $_auth_payload;

    if ($_auth_payload !== null) {
        if (hasScope($scope)) {
            return; // Branch 1: authorised
        }
        // Branch 2: authenticated but missing the required scope
        require_once __DIR__ . '/controllers/error.php';
        displayError(
            403,
            'Access denied: you do not have the ' .
            htmlspecialchars($scope, ENT_QUOTES, 'UTF-8') .
            ' permission needed for this page.'
        );
        _authExit();
        return; // unreachable; keeps static analysis happy
    }

    // Branch 3: no valid token — redirect to aithne login
    http_response_code(302);
    header('Location: ' . _buildLoginUrl());
    _authExit();
}
