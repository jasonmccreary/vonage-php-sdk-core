<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Call\Event;
use VonageTest\Fixture\ResponseTrait;

uses(ResponseTrait::class);

test('expects message', function () {
    $this->expectException('InvalidArgumentException');
    @new Event(['uuid' => 'something_unique']);
});

test('expects u u i d', function () {
    $this->expectException('InvalidArgumentException');
    @new Event(['message' => 'something happened']);
});

test('get id', function () {
    expect($this->entity->getId())->toBe('5dd627ff-caff-46a8-99ed-891e5ffebc55');
    expect($this->entity['uuid'])->toBe('5dd627ff-caff-46a8-99ed-891e5ffebc55');
});

test('get message', function () {
    expect($this->entity->getMessage())->toBe('Stream stopped');
    expect($this->entity['message'])->toBe('Stream stopped');
});

// Helpers
function setup(): void
{
    $data = test()->getResponseData(['calls', 'event']);
    test()->entity = @new Event($data);
}