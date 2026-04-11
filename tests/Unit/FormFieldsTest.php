<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

// fetchFromApi is called by getFormFields() but not getTagFields()/getTagKeys().
// Stub it so the require doesn't fail if the function isn't defined yet.
if (!function_exists('fetchFromApi')) {
    function fetchFromApi($path, $method = 'GET', $data = null, $headers = [], $timeout = null)
    {
        return [];
    }
}

require_once __DIR__ . '/../../src/formfields.php';

class FormFieldsTest extends TestCase
{
    // --- getTagFields() ---

    public function testGetTagFieldsReturnsArray(): void
    {
        $fields = getTagFields();
        $this->assertIsArray($fields);
    }

    public function testGetTagFieldsContainsExpectedKeys(): void
    {
        $fields = getTagFields();
        $expectedKeys = ['title', 'artist', 'album', 'composer', 'producer', 'rating',
                         'singalong', 'memory', 'theme_tune', 'soundtrack', 'lyrics',
                         'language', 'dance', 'provenance', 'availability', 'offence',
                         'comment', 'about', 'mentions'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $fields, "Expected tag field '$key' to exist");
        }
    }

    public function testEveryTagFieldHasAType(): void
    {
        $fields = getTagFields();
        foreach ($fields as $key => $field) {
            $this->assertArrayHasKey('type', $field, "Field '$key' is missing a 'type' key");
            $this->assertIsString($field['type'], "Field '$key' type should be a string");
            $this->assertNotEmpty($field['type'], "Field '$key' type should not be empty");
        }
    }

    public function testKnownFieldTypes(): void
    {
        $allowedTypes = ['text', 'multi-text', 'textarea', 'range', 'discrete-range',
                         'select', 'multiselect', 'multigroupselect', 'search', 'album-search'];
        $fields = getTagFields();
        foreach ($fields as $key => $field) {
            $this->assertContains(
                $field['type'],
                $allowedTypes,
                "Field '$key' has unknown type '{$field['type']}'"
            );
        }
    }

    public function testSelectAndDiscreteRangeFieldsHaveValues(): void
    {
        $fields = getTagFields();
        foreach ($fields as $key => $field) {
            if (in_array($field['type'], ['select', 'multiselect', 'multigroupselect', 'discrete-range'])) {
                $this->assertArrayHasKey('values', $field, "Field '$key' (type: {$field['type']}) should have a 'values' key");
                $this->assertIsArray($field['values'], "Field '$key' values should be an array");
                $this->assertNotEmpty($field['values'], "Field '$key' values should not be empty");
            }
        }
    }

    public function testTitleFieldIsText(): void
    {
        $fields = getTagFields();
        $this->assertSame('text', $fields['title']['type']);
    }

    public function testAlbumFieldIsAlbumSearch(): void
    {
        $fields = getTagFields();
        $this->assertSame('album-search', $fields['album']['type']);
    }

    public function testRatingFieldIsRange(): void
    {
        $fields = getTagFields();
        $this->assertSame('range', $fields['rating']['type']);
    }

    public function testSingalongFieldIsDiscreteRange(): void
    {
        $fields = getTagFields();
        $this->assertSame('discrete-range', $fields['singalong']['type']);
    }

    public function testSingalongValuesSpanZeroToFive(): void
    {
        $fields = getTagFields();
        $values = $fields['singalong']['values'];
        $this->assertArrayHasKey(0, $values);
        $this->assertArrayHasKey(5, $values);
    }

    public function testOffenceFieldIsMultigroupselect(): void
    {
        $fields = getTagFields();
        $this->assertSame('multigroupselect', $fields['offence']['type']);
    }

    // --- getTagKeys() ---

    public function testGetTagKeysReturnsArray(): void
    {
        $keys = getTagKeys();
        $this->assertIsArray($keys);
    }

    public function testGetTagKeysMatchesGetTagFieldsKeys(): void
    {
        $this->assertSame(array_keys(getTagFields()), getTagKeys());
    }

    public function testGetTagKeysAreAllStrings(): void
    {
        foreach (getTagKeys() as $key) {
            $this->assertIsString($key);
        }
    }

    public function testGetTagKeysContainsTitleAndArtist(): void
    {
        $keys = getTagKeys();
        $this->assertContains('title', $keys);
        $this->assertContains('artist', $keys);
    }

    public function testGetTagKeysDoesNotContainCollections(): void
    {
        // collections is added by getFormFields(), not getTagFields()/getTagKeys()
        $this->assertNotContains('collections', getTagKeys());
    }
}
