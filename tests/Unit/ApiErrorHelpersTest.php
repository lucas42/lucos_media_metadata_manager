<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ApiError;

require_once __DIR__ . '/../../src/api.php';

/**
 * Tests for the range-based API error helpers introduced in #311:
 *   - apiErrorToManagerStatus(ApiError): int
 *   - apiErrorReason(ApiError): string
 *   - apiErrorMessage(ApiError, string): string
 *
 * Decision (Option A, lucas42 👍):
 *   upstream 4xx → manager 500 ("our bug", don't retry)
 *   upstream 5xx / network (code=0) → manager 502 ("downstream transient", retry)
 */
class ApiErrorHelpersTest extends TestCase
{
    // -------------------------------------------------------------------------
    // apiErrorToManagerStatus
    // -------------------------------------------------------------------------

    public function testNetworkFailureCode0Returns502(): void
    {
        $error = new ApiError("Connection refused", 0);
        $this->assertSame(502, apiErrorToManagerStatus($error));
    }

    public function testUpstream400Returns500(): void
    {
        $error = new ApiError("API returned unexpected status code 400", 400);
        $this->assertSame(500, apiErrorToManagerStatus($error));
    }

    public function testUpstream422Returns500(): void
    {
        $error = new ApiError("API returned unexpected status code 422", 422);
        $this->assertSame(500, apiErrorToManagerStatus($error));
    }

    public function testUpstream499Returns500(): void
    {
        // Any 4xx that isn't specially handled by the caller maps to 500.
        $error = new ApiError("API returned unexpected status code 499", 499);
        $this->assertSame(500, apiErrorToManagerStatus($error));
    }

    public function testUpstream500Returns502(): void
    {
        $error = new ApiError("API returned unexpected status code 500", 500);
        $this->assertSame(502, apiErrorToManagerStatus($error));
    }

    public function testUpstream502Returns502(): void
    {
        $error = new ApiError("API returned unexpected status code 502", 502);
        $this->assertSame(502, apiErrorToManagerStatus($error));
    }

    public function testUpstream503Returns502(): void
    {
        $error = new ApiError("API returned unexpected status code 503", 503);
        $this->assertSame(502, apiErrorToManagerStatus($error));
    }

    public function testUnexpectedLowCodeReturns502(): void
    {
        // e.g. a redirect (3xx) leaked past fetchFromApi somehow — treat as transient
        $error = new ApiError("Unexpected status", 301);
        $this->assertSame(502, apiErrorToManagerStatus($error));
    }

    // -------------------------------------------------------------------------
    // apiErrorReason
    // -------------------------------------------------------------------------

    public function testReasonExtractedFromJsonBody(): void
    {
        $body = '{"error":"uri does not start with an allowed origin","code":"bad_request"}';
        $error = new ApiError("API returned unexpected status code 400", 400, null, $body);
        $this->assertSame("uri does not start with an allowed origin", apiErrorReason($error));
    }

    public function testReasonEmptyWhenNoResponseBody(): void
    {
        $error = new ApiError("Connection refused", 0, null, null);
        $this->assertSame('', apiErrorReason($error));
    }

    public function testReasonEmptyWhenBodyIsNotJson(): void
    {
        $error = new ApiError("API returned unexpected status code 502", 502, null, "<html>Bad Gateway</html>");
        $this->assertSame('', apiErrorReason($error));
    }

    public function testReasonEmptyWhenBodyHasNoErrorField(): void
    {
        $error = new ApiError("API returned unexpected status code 400", 400, null, '{"code":"unknown"}');
        $this->assertSame('', apiErrorReason($error));
    }

    // -------------------------------------------------------------------------
    // apiErrorMessage
    // -------------------------------------------------------------------------

    public function test500ClassMessageWithNoContext(): void
    {
        $error = new ApiError("API returned unexpected status code 400", 400);
        $msg = apiErrorMessage($error);
        $this->assertStringContainsString("Something went wrong", $msg);
        $this->assertStringNotContainsString("400", $msg); // code must not appear
    }

    public function test500ClassMessageWithContext(): void
    {
        $error = new ApiError("API returned unexpected status code 400", 400);
        $msg = apiErrorMessage($error, "Error updating track in API.");
        $this->assertStringStartsWith("Error updating track in API.", $msg);
        $this->assertStringContainsString("Something went wrong", $msg);
    }

    public function test500ClassMessageAppendsApiReason(): void
    {
        $body = '{"error":"uri does not start with an allowed origin","code":"bad_request"}';
        $error = new ApiError("API returned unexpected status code 400", 400, null, $body);
        $msg = apiErrorMessage($error, "Error updating track in API.");
        $this->assertStringContainsString("uri does not start with an allowed origin", $msg);
        $this->assertStringNotContainsString("status code 400", $msg);
    }

    public function test502ClassMessageWithContext(): void
    {
        $error = new ApiError("Connection refused", 0);
        $msg = apiErrorMessage($error, "Can't fetch tracks from API.");
        $this->assertStringStartsWith("Can't fetch tracks from API.", $msg);
        $this->assertStringContainsString("temporarily unavailable", $msg);
    }

    public function test502ClassMessageForUpstream5xx(): void
    {
        $error = new ApiError("API returned unexpected status code 503", 503);
        $msg = apiErrorMessage($error, "Error fetching album.");
        $this->assertStringContainsString("temporarily unavailable", $msg);
        $this->assertStringNotContainsString("503", $msg); // code must not appear
    }

    public function test500ClassMessageWithNoApiReasonDoesNotAppendDetail(): void
    {
        // When no parseable reason is available, the Detail line should be absent.
        $error = new ApiError("API returned unexpected status code 400", 400, null, null);
        $msg = apiErrorMessage($error);
        $this->assertStringNotContainsString("Detail:", $msg);
    }
}
