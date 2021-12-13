<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\App;

uses(VonageTestCase::class);

test('sets username at creation', function () {
    $this->assertSame("username", (new App("username"))->getId());
});

test('factory creates app endpoint', function () {
    $this->assertSame("username", App::factory('username')->getId());
});

test('to array has correct structure', function () {
    $this->assertSame([
        'type' => 'app',
        'user' => 'username',
    ], (new App("username"))->toArray());
});

test('serializes to j s o n correctly', function () {
    $this->assertSame([
        'type' => 'app',
        'user' => 'username',
    ], (new App("username"))->jsonSerialize());
});
