<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Response;
use Vonage\Response\Message;

uses(VonageTestCase::class);


beforeEach(function () {
    $this->response = new Response($this->json);
    $this->array = json_decode($this->json, true);
});

test('message count', function () {
    expect($this->response->count())->toEqual($this->array['message-count']);
    expect($this->response)->toHaveCount(->count(), $this->response);
    expect($this->response->getMessages())->toHaveCount($this->response->count());

    $count = 0;

    foreach ($this->response as $message) {
        expect($message)->toBeInstanceOf(Message::class);
        $count++;
    }

    expect($count)->toEqual($this->response->count());
});

test('throw exception when non string passed', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('expected response data to be a string');

    new Response(4);
});
