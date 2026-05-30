<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ApiError;

require_once __DIR__ . '/../../src/api.php';

/**
 * Tests for the range-based error mapping methods on ApiError introduced in #311:
 *   - ApiError::managerStatus(): int
 *   - ApiError::detail(): string
 *   - ApiError::userMessage(): string
 */
class ApiErrorHelpersTest extends TestCase
{
    // -------------------------------------------------------------------------
    // ApiError::managerStatus
    // -------------------------------------------------------------------------

    public function testNetworkFailureCode0Returns502(): void
    {
        $error = new ApiError("Connection refused", 0);
        $this->assertSame(502, $error->managerStatus());
    }

    public function testUpstream400Returns500(): void
    {
        $error = new ApiError("API returned unexpected status code 400", 400);
        $this->assertSame(500, $error->managerStatus());
    }

    public function testUpstream422Returns500(): void
    {
        $error = new ApiError("API returned unexpected status code 422", 422);
        $this->assertSame(500, $error->managerStatus());
    }

    public function testUpstream499Returns500(): void
    {
        // Any 4xx that isn't specially handled by the caller maps to 500.
        $error = new ApiError("API returned unexpected status code 499", 499);
        $this->assertSame(500, $error->managerStatus());
    }

    public function testUpstream500Returns502(): void
    {
        $error = new ApiError("API returned unexpected status code 500", 500);
        $this->assertSame(502, $error->managerStatus());
    }

    public function testUpstream502Returns502(): void
    {
        $error = new ApiError("API returned unexpected status code 502", 502);
        $this->assertSame(502, $error->managerStatus());
    }

    public function testUpstream503Returns502(): void
    {
        $error = new ApiError("API returned unexpected status code 503", 503);
        $this->assertSame(502, $error->managerStatus());
    }

    public function testUnexpectedLowCodeReturns502(): void
    {
        // e.g. a redirect (3xx) leaked past fetchFromApi somehow — treat as transient
        $error = new ApiError("Unexpected status", 301);
        $this->assertSame(502, $error->managerStatus());
    }

    // -------------------------------------------------------------------------
    // ApiError::detail
    // -------------------------------------------------------------------------

    public function testReasonExtractedFromJsonBody(): void
    {
        $body = '{"error":"uri does not start with an allowed origin","code":"bad_request"}';
        $error = new ApiError("API returned unexpected status code 400", 400, null, $body);
        $this->assertSame("uri does not start with an allowed origin", $error->detail());
    }

    public function testReasonEmptyWhenNoResponseBody(): void
    {
        $error = new ApiError("Connection refused", 0, null, null);
        $this->assertSame('', $error->detail());
    }

    public function testReasonEmptyWhenBodyIsNotJson(): void
    {
        $error = new ApiError("API returned unexpected status code 502", 502, null, "<html>Bad Gateway</html>");
        $this->assertSame('', $error->detail());
    }

    public function testReasonEmptyWhenBodyHasNoErrorField(): void
    {
        $error = new ApiError("API returned unexpected status code 400", 400, null, '{"code":"unknown"}');
        $this->assertSame('', $error->detail());
    }

    // -------------------------------------------------------------------------
    // ApiError::userMessage
    // -------------------------------------------------------------------------

    public function test500ClassUserMessage(): void
    {
        $error = new ApiError("API returned unexpected status code 400", 400);
        $msg = $error->userMessage();
        $this->assertStringContainsString("Something went wrong saving this change.", $msg);
        $this->assertStringContainsString("Retrying is unlikely to help.", $msg);
        $this->assertStringNotContainsString("400", $msg); // code must not appear
    }

    public function test502ClassUserMessage(): void
    {
        $error = new ApiError("Connection refused", 0);
        $msg = $error->userMessage();
        $this->assertStringContainsString("temporarily unavailable", $msg);
        $this->assertStringContainsString("Try again in a moment.", $msg);
    }

    public function test502ClassUserMessageForUpstream5xx(): void
    {
        $error = new ApiError("API returned unexpected status code 503", 503);
        $msg = $error->userMessage();
        $this->assertStringContainsString("temporarily unavailable", $msg);
        $this->assertStringContainsString("Try again in a moment.", $msg);
        $this->assertStringNotContainsString("503", $msg); // code must not appear
    }

    public function testDetailNotInUserMessage(): void
    {
        // detail() is returned separately — userMessage() must not embed it.
        $body = '{"error":"uri does not start with an allowed origin","code":"bad_request"}';
        $error = new ApiError("API returned unexpected status code 400", 400, null, $body);
        $this->assertStringNotContainsString("uri does not start with an allowed origin", $error->userMessage());
        $this->assertSame("uri does not start with an allowed origin", $error->detail());
    }
}
