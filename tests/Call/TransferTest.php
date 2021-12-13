<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Helmich\JsonAssert\JsonAssertions;
use Vonage\Call\Transfer;

uses(JsonAssertions::class);


test('structure with array', function () {
    $urls = ['http://example.com', 'http://alternate.example.com'];
    $schema = file_get_contents(__DIR__ . '/schema/transfer.json');
    $json = json_decode(json_encode(@new Transfer($urls)), true);

    $this->assertJsonDocumentMatchesSchema($json, json_decode(json_encode($schema), true));
    $this->assertJsonValueEquals($json, '$.action', 'transfer');
    $this->assertJsonValueEquals($json, '$.destination.type', 'ncco');
    $this->assertJsonValueEquals($json, '$.destination.url', $urls);
});

test('structure with string', function () {
    $urls = 'http://example.com';
    $schema = file_get_contents(__DIR__ . '/schema/transfer.json');
    $json = json_decode(json_encode(@new Transfer($urls)), true);

    $this->assertJsonDocumentMatchesSchema($json, json_decode(json_encode($schema), true));
    $this->assertJsonValueEquals($json, '$.action', 'transfer');
    $this->assertJsonValueEquals($json, '$.destination.type', 'ncco');
    $this->assertJsonValueEquals($json, '$.destination.url', [$urls]);
});
