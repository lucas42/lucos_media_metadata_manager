<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/fatalhandler.php';

/**
 * src/fatalhandler.php is loaded via `auto_prepend_file` (vhost.conf) on
 * every request, so it registers a shutdown function that renders the
 * friendly 500 page when a PHP fatal fires (#389). ErrorDocument 500 alone
 * doesn't reach these under mod_php — see the file's docblock for why this
 * exists — this test covers the pure detection logic; the end-to-end
 * behaviour (shutdown function firing, status code, response body) was
 * verified manually against a running container, since PHPUnit's CLI SAPI
 * has no real headers-sent state to exercise.
 */
class FatalHandlerTest extends TestCase
{
    public function testFatalErrorTypeIsUnrecoverable(): void
    {
        $this->assertTrue(isUnrecoverableFatal(['type' => E_ERROR, 'message' => 'x', 'file' => 'x', 'line' => 1]));
    }

    public function testParseErrorTypeIsUnrecoverable(): void
    {
        $this->assertTrue(isUnrecoverableFatal(['type' => E_PARSE, 'message' => 'x', 'file' => 'x', 'line' => 1]));
    }

    public function testCoreErrorTypeIsUnrecoverable(): void
    {
        $this->assertTrue(isUnrecoverableFatal(['type' => E_CORE_ERROR, 'message' => 'x', 'file' => 'x', 'line' => 1]));
    }

    public function testCompileErrorTypeIsUnrecoverable(): void
    {
        $this->assertTrue(isUnrecoverableFatal(['type' => E_COMPILE_ERROR, 'message' => 'x', 'file' => 'x', 'line' => 1]));
    }

    public function testWarningIsNotUnrecoverable(): void
    {
        // A warning doesn't halt the script — the shutdown handler firing
        // afterwards is a normal, successful request, not a fatal.
        $this->assertFalse(isUnrecoverableFatal(['type' => E_WARNING, 'message' => 'x', 'file' => 'x', 'line' => 1]));
    }

    public function testDeprecationIsNotUnrecoverable(): void
    {
        $this->assertFalse(isUnrecoverableFatal(['type' => E_DEPRECATED, 'message' => 'x', 'file' => 'x', 'line' => 1]));
    }

    public function testNoErrorIsNotUnrecoverable(): void
    {
        // error_get_last() returns null when nothing has gone wrong —
        // the overwhelmingly common case, run on every successful request.
        $this->assertFalse(isUnrecoverableFatal(null));
    }

    public function testHasNoApplicationBootstrapDependency(): void
    {
        // This file runs on every request via auto_prepend_file, so it's
        // especially important it stays dependency-free: a require of
        // authentication.php or api.php here would mean every page pays
        // that cost twice, and if either broke, every page would break
        // harder rather than getting a friendly error. errorpage.php is
        // the one permitted dependency (see its own docblock for why it's
        // safe). Strip comments first so the check isn't fooled by prose.
        $source = file_get_contents(__DIR__ . '/../../src/fatalhandler.php');
        $code = preg_replace('#/\*.*?\*/#s', '', $source);
        $this->assertStringNotContainsString('authentication.php', $code);
        $this->assertStringNotContainsString('api.php', $code);
        $this->assertDoesNotMatchRegularExpression('#views/#', $code);
        $this->assertStringContainsString('errorpage.php', $code);
    }
}
