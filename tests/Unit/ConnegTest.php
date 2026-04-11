<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/conneg.php';

class ConnegTest extends TestCase
{
    private function setAccept(string $accept): void
    {
        $_SERVER['HTTP_ACCEPT'] = $accept;
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_ACCEPT']);
    }

    // --- choose_json_over_html ---

    public function testJsonPreferredWhenAcceptJsonOnly(): void
    {
        $this->setAccept('application/json');
        $this->assertTrue(choose_json_over_html());
    }

    public function testHtmlPreferredOverJsonForBrowserAcceptHeader(): void
    {
        // A browser request — no explicit JSON, should not trigger the JSON path
        $this->setAccept('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
        $this->assertFalse(choose_json_over_html());
    }

    public function testJsonNotPreferredWhenAcceptIsEmpty(): void
    {
        $this->setAccept('');
        $this->assertFalse(choose_json_over_html());
    }

    public function testJsonNotPreferredWhenHtmlAndJsonEqualWeight(): void
    {
        // Ties go to HTML so the page remains human-viewable by default
        $this->setAccept('application/json,text/html');
        $this->assertFalse(choose_json_over_html());
    }

    public function testJsonPreferredWhenJsonHasHigherQvalue(): void
    {
        $this->setAccept('text/html;q=0.5,application/json;q=0.9');
        $this->assertTrue(choose_json_over_html());
    }

    public function testJsonNotPreferredWhenWildcardAcceptHeader(): void
    {
        // Catch-all wildcard — not an explicit JSON preference
        $this->setAccept('*/*');
        $this->assertFalse(choose_json_over_html());
    }
}
