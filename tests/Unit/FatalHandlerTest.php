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
 * exists. Most of this covers the pure detection logic in-process; the
 * buffered-output regression test below shells out to a fresh PHP process
 * instead, since it needs a genuine fatal and real output-buffering state
 * rather than a faked error_get_last() value. Full request/response
 * behaviour (actual HTTP status and headers) was verified manually against
 * a running container, since PHPUnit's CLI SAPI has no real headers-sent
 * state to exercise.
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

    public function testDiscardsBufferedPartialOutputBeforeRenderingFriendlyPage(): void
    {
        // A genuine fatal in a fresh subprocess, rather than a faked
        // error_get_last() state, so real output-buffering applies exactly
        // as it does under mod_php. CLI's own output_buffering default
        // differs from php.ini-production's, so it's set explicitly here to
        // match: this is the scenario the review on #391 caught — with
        // output_buffering on, a fatal that fires after some markup has
        // already been echoed (nav/header content, typically, before the
        // view reaches whatever fatals) left that partial markup sitting in
        // the buffer, and the friendly page got appended after it rather
        // than replacing it.
        $script = 'require ' . var_export(__DIR__ . '/../../src/fatalhandler.php', true) . ';'
            . 'echo "<html>partial nav already echoed before the crash</html>";'
            . 'undefinedFunctionToForceAFatal();';

        $output = shell_exec(
            'php -d output_buffering=4096 -r ' . escapeshellarg($script) . ' 2>/dev/null'
        );

        $this->assertStringNotContainsString('partial nav already echoed', $output);
        $this->assertStringContainsString("Something's gone wrong", $output);
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
