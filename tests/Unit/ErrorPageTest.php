<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/errorpage.php';

/**
 * src/errorpage.php renders the friendly 500 page used by both
 * fatalhandler.php's shutdown handler and html/500.php's ErrorDocument
 * fallback (#389). It must stay dependency-free — see its docblock — since
 * both call sites can be reached when the app's own bootstrap or a view
 * partial is what's broken.
 */
class ErrorPageTest extends TestCase
{
    private function renderErrorPage(): string
    {
        ob_start();
        renderFatalErrorPage();
        return ob_get_clean();
    }

    public function testRendersPlainLanguageMessage(): void
    {
        $html = $this->renderErrorPage();
        $this->assertStringContainsString("Something's gone wrong", $html);
    }

    public function testDoesNotLeakTechnicalDetail(): void
    {
        $html = $this->renderErrorPage();
        $this->assertStringNotContainsString('mb_strlen', $html);
        $this->assertStringNotContainsString('Fatal error', $html);
        $this->assertStringNotContainsString('/srv/metadata_manager', $html);
    }

    public function testHasNoDependencies(): void
    {
        // A source-text check rather than just a docblock, so this test fails
        // loudly if a future edit reintroduces a require()/include() here —
        // exactly the dependency #389 removes. Strip comments first so the
        // check isn't fooled by prose mentioning these filenames.
        $source = file_get_contents(__DIR__ . '/../../src/errorpage.php');
        $code = preg_replace('#/\*.*?\*/#s', '', $source);
        $this->assertDoesNotMatchRegularExpression('/\b(require|include)(_once)?\b/', $code);
    }
}
