<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\VBC;

uses(VonageTestCase::class);

test('sets extension at creation', function () {
    expect((new VBC('123'))->getId())->toBe('123');
});

test('factory creates v b c endpoint', function () {
    $this->assertSame('123', (VBC::factory('123'))->getId());
});

test('to array has correct structure', function () {
    $this->assertSame([
        'type' => 'vbc',
        'extension' => '123',
    ], (new VBC('123'))->toArray());
});

test('serializes to j s o n correctly', function () {
    $this->assertSame([
        'type' => 'vbc',
        'extension' => '123',
    ], (new VBC('123'))->jsonSerialize());
});
