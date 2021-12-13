<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Helmich\JsonAssert\JsonAssertions;
use VonageTest\VonageTestCase;
use Vonage\Call\Earmuff;

uses(VonageTestCase::class);
uses(JsonAssertions::class);


test('structure', function () {
    $schema = file_get_contents(__DIR__ . '/schema/earmuff.json');
    $json = json_decode(json_encode(@new Earmuff()), true);

    $this->assertJsonDocumentMatchesSchema($json, json_decode(json_encode($schema), true));
    $this->assertJsonValueEquals($json, '$.action', 'earmuff');
});
