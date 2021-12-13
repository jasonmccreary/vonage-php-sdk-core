<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Vonage\Voice\Endpoint\App;

test('sets username at creation', function () {
    expect((new App("username"))->getId())->toBe("username");
});

test('factory creates app endpoint', function () {
    expect(App::factory('username')->getId())->toBe("username");
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
