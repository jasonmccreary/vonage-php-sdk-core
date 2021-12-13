<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Response\Message;



test('success', function () {
    $json = '{
       "status":"0",
       "message-id":"00000123",
       "to":"44123456789",
       "remaining-balance":"1.10",
       "message-price":"0.05",
       "network":"23410"
    }';

    $this->message = new Message(json_decode($json, true)); //response already has decoded

    expect($this->message->getStatus())->toEqual(0);
    expect($this->message->getId())->toEqual('00000123');
    expect($this->message->getTo())->toEqual('44123456789');
    expect($this->message->getBalance())->toEqual('1.10');
    expect($this->message->getPrice())->toEqual('0.05');
    expect($this->message->getNetwork())->toEqual('23410');
    expect($this->message->getErrorMessage())->toBeEmpty();
});

test('fail', function () {
    $json = '{
       "status":"2",
       "error-text":"Missing from param"
    }';

    $this->message = new Message(json_decode($json, true)); //response already has decoded

    expect($this->message->getStatus())->toEqual(2);
    expect($this->message->getErrorMessage())->toEqual('Missing from param');

    foreach (['getId', 'getTo', 'getBalance', 'getPrice', 'getNetwork'] as $getter) {
        try {
            $this->message->$getter();

            self::fail('Trying to access ' . $getter . ' should have caused an exception');
        } catch (RuntimeException $e) {
        }
    }
});
