<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__ . '/../../src/controllers/updatetrack.php';

class FormValueToV3Test extends TestCase
{
    // --- text / default field type ---

    public function testTextFieldStringValue(): void
    {
        $result = formValueToV3('Hello', ['type' => 'text']);
        $this->assertSame([['name' => 'Hello']], $result);
    }

    public function testTextFieldEmptyString(): void
    {
        $result = formValueToV3('', ['type' => 'text']);
        $this->assertSame([], $result);
    }

    public function testTextFieldNull(): void
    {
        $result = formValueToV3(null, ['type' => 'text']);
        $this->assertSame([], $result);
    }

    public function testDefaultTypeWhenConfigMissing(): void
    {
        // No 'type' key → defaults to "text" behaviour
        $result = formValueToV3('value', []);
        $this->assertSame([['name' => 'value']], $result);
    }

    // --- multi-text field ---

    public function testMultiTextFieldArrayOfStrings(): void
    {
        $result = formValueToV3(['Alice', 'Bob'], ['type' => 'multi-text']);
        $this->assertSame([['name' => 'Alice'], ['name' => 'Bob']], $result);
    }

    public function testMultiTextFieldFiltersEmptyStrings(): void
    {
        $result = formValueToV3(['Alice', '', 'Bob'], ['type' => 'multi-text']);
        $this->assertSame([['name' => 'Alice'], ['name' => 'Bob']], $result);
    }

    public function testMultiTextFieldFiltersNullValues(): void
    {
        $result = formValueToV3(['Alice', null, 'Bob'], ['type' => 'multi-text']);
        $this->assertSame([['name' => 'Alice'], ['name' => 'Bob']], $result);
    }

    public function testMultiTextFieldAllEmpty(): void
    {
        $result = formValueToV3(['', ''], ['type' => 'multi-text']);
        $this->assertSame([], $result);
    }

    // --- search field (URI field) ---

    public function testSearchFieldStringValue(): void
    {
        // Scalar value on a search field: name and uri both set to the value
        $result = formValueToV3('https://example.com/en/', ['type' => 'search']);
        $this->assertSame([['name' => 'https://example.com/en/', 'uri' => 'https://example.com/en/']], $result);
    }

    public function testSearchFieldEmptyString(): void
    {
        $result = formValueToV3('', ['type' => 'search']);
        $this->assertSame([], $result);
    }

    public function testSearchFieldNull(): void
    {
        $result = formValueToV3(null, ['type' => 'search']);
        $this->assertSame([], $result);
    }

    public function testSearchFieldStructuredArray(): void
    {
        // lucos-search injects indexed structured pairs: ['uri' => ..., 'name' => ...]
        $value = [
            ['uri' => 'https://example.com/en/', 'name' => 'English'],
            ['uri' => 'https://example.com/ga/', 'name' => 'Irish'],
        ];
        $result = formValueToV3($value, ['type' => 'search']);
        $this->assertSame([
            ['name' => 'English', 'uri' => 'https://example.com/en/'],
            ['name' => 'Irish',   'uri' => 'https://example.com/ga/'],
        ], $result);
    }

    public function testSearchFieldStructuredArrayFallsBackUriAsName(): void
    {
        // When 'name' is missing, uri is used as the name
        $value = [['uri' => 'https://example.com/en/']];
        $result = formValueToV3($value, ['type' => 'search']);
        $this->assertSame([['name' => 'https://example.com/en/', 'uri' => 'https://example.com/en/']], $result);
    }

    public function testSearchFieldStructuredArraySkipsEmptyUri(): void
    {
        $value = [
            ['uri' => '', 'name' => 'Should be skipped'],
            ['uri' => 'https://example.com/en/', 'name' => 'English'],
        ];
        $result = formValueToV3($value, ['type' => 'search']);
        $this->assertSame([['name' => 'English', 'uri' => 'https://example.com/en/']], $result);
    }

    public function testSearchFieldMixedArrayTreatsPlainStringsAsNameOnly(): void
    {
        // Plain strings in a search field array are not structured, so treated as name-only
        $value = ['English'];
        $result = formValueToV3($value, ['type' => 'search']);
        $this->assertSame([['name' => 'English']], $result);
    }

    // --- album-search field ---

    public function testAlbumSearchFieldWithUri(): void
    {
        // album-search submits a URI; name is resolved server-side by the API
        $result = formValueToV3('/albums/3', ['type' => 'album-search']);
        $this->assertSame([['uri' => '/albums/3']], $result);
    }

    public function testAlbumSearchFieldWithAbsoluteUri(): void
    {
        $result = formValueToV3('https://media-metadata.l42.eu/albums/7', ['type' => 'album-search']);
        $this->assertSame([['uri' => 'https://media-metadata.l42.eu/albums/7']], $result);
    }

    public function testAlbumSearchFieldEmptyString(): void
    {
        $result = formValueToV3('', ['type' => 'album-search']);
        $this->assertSame([], $result);
    }

    public function testAlbumSearchFieldNull(): void
    {
        $result = formValueToV3(null, ['type' => 'album-search']);
        $this->assertSame([], $result);
    }
}
