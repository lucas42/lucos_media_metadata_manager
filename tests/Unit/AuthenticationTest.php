<?php

declare(strict_types=1);

/**
 * Global-namespace block: stubs must land in \displayError, not Tests\Unit\displayError.
 * The function guards in controllers/error.php prevent redeclaration when
 * authentication.php later triggers require_once of that file.
 */
namespace {
    $GLOBALS['_last_error_status'] = null;

    if (!function_exists('displayError')) {
        function displayError(int $statusCode, string $message, ...$rest): void
        {
            $GLOBALS['_last_error_status'] = $statusCode;
            // No view rendering in unit tests
        }
    }

    if (!function_exists('displayApiError')) {
        function displayApiError(object $error, string $context, ...$rest): void
        {
            // No-op in unit tests
        }
    }

    if (!function_exists('csrfTokenField')) {
        function csrfTokenField(): string
        {
            return '';
        }
    }

    // Bootstrap authentication module once for the whole test run.
    // Module-level code reads $_COOKIE (empty in CLI) → $_auth_payload stays null.
    $_COOKIE = [];
    require_once __DIR__ . '/../../src/authentication.php';
}

// ------------------------------------------------------------------
// Test class
// ------------------------------------------------------------------

namespace Tests\Unit {
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    use PHPUnit\Framework\TestCase;

    class AuthenticationTest extends TestCase
    {
        /** @var resource  OpenSSL EC private key for test token signing */
        private $privateKey;

        /** @var string  PEM-encoded public key for test verification */
        private string $publicKey;

        /** @var array<string,mixed>  Raw key details (for constructing JWK arrays in fallback tests) */
        private array $keyDetails;

        protected function setUp(): void
        {
            // Generate a fresh ES256 (P-256) key pair for each test
            $keyPair = openssl_pkey_new([
                'curve_name'       => 'prime256v1',
                'private_key_type' => OPENSSL_KEYTYPE_EC,
            ]);
            openssl_pkey_export($keyPair, $this->privateKey);
            $details           = openssl_pkey_get_details($keyPair);
            $this->publicKey   = $details['key'];
            $this->keyDetails  = $details;

            // Inject test key set so _getJwksKeys() bypasses network
            $GLOBALS['_test_jwks_keys'] = ['test-kid' => new Key($this->publicKey, 'ES256')];

            // Reset auth payload so each test starts unauthenticated
            $GLOBALS['_auth_payload'] = null;

            // Clear exit hook and error-status tracker
            unset($GLOBALS['_test_exit_fn']);
            $GLOBALS['_last_error_status'] = null;

            // Reset env vars to defaults
            putenv('AITHNE_ORIGIN=https://aithne.l42.eu');
            putenv('ENVIRONMENT=');
            putenv('APP_ORIGIN=https://media-metadata.l42.eu');
            $GLOBALS['AITHNE_ORIGIN'] = 'https://aithne.l42.eu';
            $GLOBALS['APP_ORIGIN']    = 'https://media-metadata.l42.eu';
        }

        protected function tearDown(): void
        {
            unset($GLOBALS['_test_jwks_keys']);
            unset($GLOBALS['_test_exit_fn']);
            unset($GLOBALS['_test_cached_raw_jwks']);
            unset($GLOBALS['_test_force_jwks_fetch_failure']);
            $GLOBALS['_auth_payload'] = null;
            // Clear the static JWKS cache so each test starts with a clean slate
            $GLOBALS['_test_reset_jwks_cache'] = true;
        }

        // -----------------------------------------------------------------------
        // Helpers
        // -----------------------------------------------------------------------

        /**
         * Build and sign a JWT with the test private key.
         *
         * @param array<string,mixed> $overrides  Overrides for default claims.
         */
        private function makeToken(array $overrides = []): string
        {
            $defaults = [
                'iss'             => 'https://aithne.l42.eu',
                'aud'             => 'l42.eu',
                'sub'             => 'test-user',
                'iat'             => time() - 1,
                'exp'             => time() + 3600,
                'scopes'          => ['media-metadata:read'],
                'principal_class' => 'human',   // contract §5
            ];
            return JWT::encode(
                array_merge($defaults, $overrides),
                $this->privateKey,
                'ES256',
                'test-kid'
            );
        }

        /**
         * Build a raw JWKS array for the current test key pair.
         * Used to pre-seed the static JWKS cache in fallback tests.
         *
         * @return array<string,mixed>
         */
        private function buildRawJwks(): array
        {
            $ec = $this->keyDetails['ec'];
            return [
                'keys' => [
                    [
                        'kty' => 'EC',
                        'crv' => 'P-256',
                        'x'   => rtrim(strtr(base64_encode(str_pad($ec['x'], 32, "\0", STR_PAD_LEFT)), '+/', '-_'), '='),
                        'y'   => rtrim(strtr(base64_encode(str_pad($ec['y'], 32, "\0", STR_PAD_LEFT)), '+/', '-_'), '='),
                        'kid' => 'test-kid',
                        'use' => 'sig',
                        'alg' => 'ES256',
                    ]
                ]
            ];
        }

        // -----------------------------------------------------------------------
        // _verifyAithneToken
        // -----------------------------------------------------------------------

        public function testValidTokenReturnsPayload(): void
        {
            $token   = $this->makeToken(['scopes' => ['media-metadata:read', 'media-metadata:write']]);
            $payload = _verifyAithneToken($token);

            $this->assertIsArray($payload);
            $this->assertSame('test-user', $payload['sub']);
            $this->assertContains('media-metadata:read', $payload['scopes']);
            $this->assertContains('media-metadata:write', $payload['scopes']);
        }

        public function testExpiredTokenReturnsNull(): void
        {
            // exp in the past, beyond the 30-second leeway
            $token   = $this->makeToken(['exp' => time() - 60]);
            $payload = _verifyAithneToken($token);

            $this->assertNull($payload);
        }

        public function testWrongIssReturnsNull(): void
        {
            $token   = $this->makeToken(['iss' => 'https://evil.example.com']);
            $payload = _verifyAithneToken($token);

            $this->assertNull($payload);
        }

        public function testWrongAudReturnsNull(): void
        {
            $token   = $this->makeToken(['aud' => 'other.example.com']);
            $payload = _verifyAithneToken($token);

            $this->assertNull($payload);
        }

        public function testInvalidSignatureReturnsNull(): void
        {
            // Tamper with the payload segment to invalidate the signature
            $token  = $this->makeToken();
            $parts  = explode('.', $token);
            $parts[1] = base64_encode(
                '{"iss":"https://aithne.l42.eu","aud":"l42.eu","sub":"hacker","exp":9999999999}'
            );
            $this->assertNull(_verifyAithneToken(implode('.', $parts)));
        }

        public function testAudAsArrayIsAccepted(): void
        {
            // l42.eu present among multiple aud values
            $token   = $this->makeToken(['aud' => ['l42.eu', 'other.example.com']]);
            $payload = _verifyAithneToken($token);
            $this->assertIsArray($payload);
        }

        public function testUnrecognisedPrincipalClassReturnsNull(): void
        {
            $token   = $this->makeToken(['principal_class' => 'service']);
            $payload = _verifyAithneToken($token);
            $this->assertNull($payload);
        }

        public function testAgentPrincipalClassIsAccepted(): void
        {
            $token   = $this->makeToken(['principal_class' => 'agent']);
            $payload = _verifyAithneToken($token);
            $this->assertIsArray($payload);
            $this->assertSame('agent', $payload['principal_class']);
        }

        public function testJwksFallbackServesLastKnownGoodKeySet(): void
        {
            // Pre-seed the static JWKS cache with a raw JWK for our test key,
            // then simulate a fetch failure: the cache must carry the request.
            $GLOBALS['_test_cached_raw_jwks']          = $this->buildRawJwks();
            $GLOBALS['_test_force_jwks_fetch_failure'] = true;

            try {
                // Remove the direct-key bypass so _getJwksKeys() runs the real path
                unset($GLOBALS['_test_jwks_keys']);

                $token   = $this->makeToken();
                $payload = _verifyAithneToken($token);

                $this->assertIsArray($payload, 'Cached key set must allow token verification on fetch failure');
                $this->assertSame('test-user', $payload['sub']);
            } finally {
                // Restore the direct-key bypass and clean up test state
                $GLOBALS['_test_jwks_keys'] = ['test-kid' => new Key($this->publicKey, 'ES256')];
                unset($GLOBALS['_test_force_jwks_fetch_failure']);
                $GLOBALS['_test_reset_jwks_cache'] = true;
            }
        }

        // -----------------------------------------------------------------------
        // hasScope
        // -----------------------------------------------------------------------

        public function testHasScopeReturnsFalseWhenNotAuthenticated(): void
        {
            $GLOBALS['_auth_payload'] = null;
            $this->assertFalse(hasScope('media-metadata:read'));
        }

        public function testHasScopeReturnsTrueForExactScopeMatch(): void
        {
            $GLOBALS['_auth_payload'] = ['scopes' => ['media-metadata:read']];
            $this->assertTrue(hasScope('media-metadata:read'));
        }

        public function testHasScopeReturnsFalseForMissingScope(): void
        {
            $GLOBALS['_auth_payload'] = ['scopes' => ['media-metadata:read']];
            $this->assertFalse(hasScope('media-metadata:write'));
        }

        public function testHasScopeReturnsTrueForWriteWhenWriteScopePresent(): void
        {
            $GLOBALS['_auth_payload'] = [
                'scopes' => ['media-metadata:read', 'media-metadata:write'],
            ];
            $this->assertTrue(hasScope('media-metadata:write'));
        }

        public function testRenderUiBypassActivatesAllScopesInDevelopment(): void
        {
            putenv('ENVIRONMENT=development');
            $GLOBALS['_auth_payload'] = ['scopes' => ['render-ui']];

            $this->assertTrue(hasScope('media-metadata:read'), 'render-ui should bypass read in dev');
            $this->assertTrue(hasScope('media-metadata:write'), 'render-ui should bypass write in dev');
        }

        public function testRenderUiBypassDoesNotActivateInProduction(): void
        {
            putenv('ENVIRONMENT=production');
            $GLOBALS['_auth_payload'] = ['scopes' => ['render-ui']];

            $this->assertFalse(hasScope('media-metadata:read'), 'render-ui must not bypass in production');
            $this->assertFalse(hasScope('media-metadata:write'), 'render-ui must not bypass in production');
        }

        // -----------------------------------------------------------------------
        // requireScope — Branch 1 (authorised: returns without exception)
        // -----------------------------------------------------------------------

        public function testRequireScopeProceedsWhenScopePresent(): void
        {
            $GLOBALS['_auth_payload'] = ['scopes' => ['media-metadata:read']];
            requireScope('media-metadata:read');
            $this->assertTrue(true); // reached here = branch 1 worked
        }

        public function testRequireScopeWriteProceedsWhenWriteScopePresent(): void
        {
            $GLOBALS['_auth_payload'] = [
                'scopes' => ['media-metadata:read', 'media-metadata:write'],
            ];
            requireScope('media-metadata:write');
            $this->assertTrue(true);
        }

        // -----------------------------------------------------------------------
        // requireScope — Branch 2 (authenticated, missing scope → 403)
        // -----------------------------------------------------------------------

        public function testRequireScopeProduces403WhenScopeMissing(): void
        {
            $GLOBALS['_auth_payload'] = ['scopes' => ['media-metadata:read']];

            $exitCalled = false;
            $GLOBALS['_test_exit_fn'] = function () use (&$exitCalled): void {
                $exitCalled = true;
                throw new \RuntimeException('_authExit called');
            };

            try {
                requireScope('media-metadata:write');
                $this->fail('Expected _authExit to be called');
            } catch (\RuntimeException $e) {
                $this->assertSame('_authExit called', $e->getMessage());
            }

            $this->assertTrue($exitCalled, '_authExit must be called on branch 2');
            $this->assertSame(403, $GLOBALS['_last_error_status'], 'displayError must receive 403');
        }

        // -----------------------------------------------------------------------
        // requireScope — Branch 3 (no valid token → 302 redirect)
        // -----------------------------------------------------------------------

        public function testRequireScopeRedirectsWhenNotAuthenticated(): void
        {
            $GLOBALS['_auth_payload'] = null;
            $_SERVER['REQUEST_URI']  = '/tracks/42';

            $exitCalled = false;
            $GLOBALS['_test_exit_fn'] = function () use (&$exitCalled): void {
                $exitCalled = true;
                throw new \RuntimeException('_authExit called');
            };

            try {
                requireScope('media-metadata:read');
                $this->fail('Expected _authExit to be called');
            } catch (\RuntimeException $e) {
                $this->assertSame('_authExit called', $e->getMessage());
            }

            $this->assertTrue($exitCalled, '_authExit must be called on branch 3');
            // header() and http_response_code() are not inspectable in CLI mode;
            // redirect URL correctness is verified by _buildLoginUrl() logic.
        }

        // -----------------------------------------------------------------------
        // _buildLoginUrl() — next param must be a full URL, not just a path
        // -----------------------------------------------------------------------

        /**
         * Without APP_ORIGIN, aithne would redirect the user to a path on its
         * own domain (e.g. aithne.l42.eu/tracks/42) instead of back to
         * media-metadata.l42.eu/tracks/42.  This test asserts that APP_ORIGIN is
         * prepended so the `next` parameter is always a fully-qualified URL.
         */
        public function testBuildLoginUrlPrependsAppOriginToNext(): void
        {
            $GLOBALS['AITHNE_ORIGIN'] = 'https://aithne.l42.eu';
            $GLOBALS['APP_ORIGIN']    = 'https://media-metadata.l42.eu';
            $_SERVER['REQUEST_URI']   = '/tracks/42';

            $url = _buildLoginUrl();

            $expectedNext = 'https://media-metadata.l42.eu/tracks/42';
            $this->assertStringContainsString(
                'next=' . rawurlencode($expectedNext),
                $url,
                '_buildLoginUrl() must include APP_ORIGIN in the next= param so aithne redirects back to this service'
            );
            $this->assertStringStartsWith(
                'https://aithne.l42.eu/auth/login',
                $url
            );
        }

        public function testBuildLoginUrlGuardsOpenRedirect(): void
        {
            $GLOBALS['AITHNE_ORIGIN'] = 'https://aithne.l42.eu';
            $GLOBALS['APP_ORIGIN']    = 'https://media-metadata.l42.eu';
            // An attacker-supplied REQUEST_URI with a full URL must be rejected
            $_SERVER['REQUEST_URI']   = 'https://evil.example.com/steal';

            $url = _buildLoginUrl();

            // next must fall back to APP_ORIGIN + '/' not to the attacker URL
            $this->assertStringContainsString(
                'next=' . rawurlencode('https://media-metadata.l42.eu/'),
                $url
            );
            $this->assertStringNotContainsString('evil.example.com', $url);
        }

        // -----------------------------------------------------------------------
        // /_info exemption: verify it never requires authentication.php
        // -----------------------------------------------------------------------

        public function testInfoEndpointDoesNotIncludeAuthenticationModule(): void
        {
            $infoPhpPath = __DIR__ . '/../../src/html/_info.php';
            $this->assertFileExists($infoPhpPath);

            $content = file_get_contents($infoPhpPath);
            $this->assertStringNotContainsString(
                'authentication.php',
                $content,
                '/_info.php must remain auth-exempt — never require authentication.php'
            );
        }
    }
}
