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

uses(VonageTestCase::class);
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
    $this->assertSame('5dd627ff-caff-46a8-99ed-891e5ffebc55', $this->entity->getId());
    $this->assertSame('5dd627ff-caff-46a8-99ed-891e5ffebc55', $this->entity['uuid']);
});

test('get message', function () {
    $this->assertSame('Stream stopped', $this->entity->getMessage());
    $this->assertSame('Stream stopped', $this->entity['message']);
});

// Helpers
function setup(): void
{
    $data = test()->getResponseData(['calls', 'event']);
    test()->entity = @new Event($data);
}
