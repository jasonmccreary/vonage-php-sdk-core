<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use InvalidArgumentException;
use VonageTest\VonageTestCase;
use Vonage\Response;
use Vonage\Response\Message;

uses(VonageTestCase::class);

use function json_decode;

beforeEach(function () {
    $this->response = new Response($this->json);
    $this->array = json_decode($this->json, true);
});

test('message count', function () {
    $this->assertEquals($this->array['message-count'], $this->response->count());
    $this->assertCount($this->response->count(), $this->response);
    $this->assertCount($this->response->count(), $this->response->getMessages());

    $count = 0;

    foreach ($this->response as $message) {
        $this->assertInstanceOf(Message::class, $message);
        $count++;
    }

    $this->assertEquals($this->response->count(), $count);
});

test('throw exception when non string passed', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('expected response data to be a string');

    new Response(4);
});
