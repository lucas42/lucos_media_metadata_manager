<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Renders src/views/field.php end-to-end. field.php calls mb_strlen() on every
 * render (#387) — this is what a missing mbstring extension breaks in production
 * without failing the build or the healthcheck.
 */
class FieldViewTest extends TestCase
{
    private function renderField(string $key, array $field, ?array $values = null): string
    {
        ob_start();
        include __DIR__ . '/../../src/views/field.php';
        return ob_get_clean();
    }

    public function testTextFieldRendersKeyLabelClass(): void
    {
        $html = $this->renderField('title', ['type' => 'text']);
        $this->assertStringContainsString('class="key-label"', $html);
        $this->assertStringContainsString('name="title"', $html);
    }

    public function testLongMultibyteKeyGetsLongKeyClass(): void
    {
        // 13 mb_strlen() characters, but far more bytes — only passes if the render
        // path genuinely counts characters via mbstring, not a byte-counting substitute.
        $key = str_repeat('☃', 13);
        $html = $this->renderField($key, ['type' => 'text']);
        $this->assertStringContainsString('class="key-label long-key"', $html);
    }
}
