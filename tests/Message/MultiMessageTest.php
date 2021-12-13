<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Response;
use VonageTest\VonageTestCase;
use Vonage\Message\Message;

uses(VonageTestCase::class);


/**
 * Test that split messages allow access to all the underlying messages. The response from sending a message is the
 * only time a message may contain multiple 'parts'. When fetched from the API, each message is separate.
 */
beforeEach(function () {
    $this->message = new Message($this->to, $this->from, [
        'text' => $this->text
    ]);
});

afterEach(function () {
    $this->message = null;
});

/**
 * Common optional params can be set
 *
 *
 * @param $size
 * @param null $response
 */
test('can count response messages', function ($size, $response = null) {
    if ($response) {
        @$this->message->setResponse($response);
    }

    expect($this->message)->toHaveCount($size);
})->with('responseSizes');

test('can access last message as array', function () {
    @$this->message->setResponse(getResponse('multi'));

    expect(@$this->message['status'])->toEqual('0');
    expect(@$this->message['message-id'])->toEqual('00000126');
    expect(@$this->message['to'])->toEqual('44123456789');
    expect(@$this->message['remaining-balance'])->toEqual('1.00');
    expect(@$this->message['message-price'])->toEqual('0.05');
    expect(@$this->message['network'])->toEqual('23410');
});

test('can access any message as array', function () {
    @$this->message->setResponse(getResponse('multi'));

    expect(@$this->message[0]['message-id'])->toEqual('00000124');
    expect(@$this->message[1]['message-id'])->toEqual('00000125');
    expect(@$this->message[2]['message-id'])->toEqual('00000126');
    expect(@$this->message[0]['remaining-balance'])->toEqual('1.10');
    expect(@$this->message[1]['remaining-balance'])->toEqual('1.05');
    expect(@$this->message[2]['remaining-balance'])->toEqual('1.00');
});

/**
 * @throws Exception
 */
test('can access last message as object', function () {
    @$this->message->setResponse(getResponse('multi'));

    expect($this->message->getStatus())->toEqual('0');
    expect($this->message->getMessageId())->toEqual('00000126');
    expect($this->message->getTo())->toEqual('44123456789');
    expect($this->message->getRemainingBalance())->toEqual('1.00');
    expect($this->message->getPrice())->toEqual('0.05');
    expect($this->message->getNetwork())->toEqual('23410');
});

/**
 * @throws Exception
 */
test('can access any messages as object', function () {
    @$this->message->setResponse(getResponse('multi'));

    expect($this->message->getMessageId(0))->toEqual('00000124');
    expect($this->message->getMessageId(1))->toEqual('00000125');
    expect($this->message->getMessageId(2))->toEqual('00000126');
    expect($this->message->getRemainingBalance(0))->toEqual('1.10');
    expect($this->message->getRemainingBalance(1))->toEqual('1.05');
    expect($this->message->getRemainingBalance(2))->toEqual('1.00');
});

test('can iterate over message parts', function () {
    foreach ($this->message as $index => $part) {
        self::fail('should not be able to iterate over empty message');
    }

    @$this->message->setResponse(getResponse('multi'));

    $iterated = false;
    foreach ($this->message as $index => $part) {
        $iterated = true;
        expect($part['status'])->toEqual('0');
        expect($part['to'])->toEqual('44123456789');
        expect($part['network'])->toEqual('23410');
        expect($part['message-price'])->toEqual('0.05');

        switch ($index) {
            case 0:
                expect($part['message-id'])->toEqual('00000124');
                expect($part['remaining-balance'])->toEqual('1.10');
                break;
            case 1:
                expect($part['message-id'])->toEqual('00000125');
                expect($part['remaining-balance'])->toEqual('1.05');
                break;
            case 2:
                expect($part['message-id'])->toEqual('00000126');
                expect($part['remaining-balance'])->toEqual('1.00');
                break;
        }
    }

    if (!$iterated) {
        self::fail('did not iterate over message with parts');
    }
});

// Datasets
/**
 * @return array[]
 */
dataset('responseSizes', [
    [0, null],
    [1, getResponse()],
    [3, getResponse('multi')]
]);

// Helpers
/**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     */
function getResponse(string $type = 'success'): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
}
