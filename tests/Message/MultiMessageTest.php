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

    $this->assertCount($size, $this->message);
})->with('responseSizes');

test('can access last message as array', function () {
    @$this->message->setResponse(getResponse('multi'));

    $this->assertEquals('0', @$this->message['status']);
    $this->assertEquals('00000126', @$this->message['message-id']);
    $this->assertEquals('44123456789', @$this->message['to']);
    $this->assertEquals('1.00', @$this->message['remaining-balance']);
    $this->assertEquals('0.05', @$this->message['message-price']);
    $this->assertEquals('23410', @$this->message['network']);
});

test('can access any message as array', function () {
    @$this->message->setResponse(getResponse('multi'));

    $this->assertEquals('00000124', @$this->message[0]['message-id']);
    $this->assertEquals('00000125', @$this->message[1]['message-id']);
    $this->assertEquals('00000126', @$this->message[2]['message-id']);
    $this->assertEquals('1.10', @$this->message[0]['remaining-balance']);
    $this->assertEquals('1.05', @$this->message[1]['remaining-balance']);
    $this->assertEquals('1.00', @$this->message[2]['remaining-balance']);
});

/**
 * @throws Exception
 */
test('can access last message as object', function () {
    @$this->message->setResponse(getResponse('multi'));

    $this->assertEquals('0', $this->message->getStatus());
    $this->assertEquals('00000126', $this->message->getMessageId());
    $this->assertEquals('44123456789', $this->message->getTo());
    $this->assertEquals('1.00', $this->message->getRemainingBalance());
    $this->assertEquals('0.05', $this->message->getPrice());
    $this->assertEquals('23410', $this->message->getNetwork());
});

/**
 * @throws Exception
 */
test('can access any messages as object', function () {
    @$this->message->setResponse(getResponse('multi'));

    $this->assertEquals('00000124', $this->message->getMessageId(0));
    $this->assertEquals('00000125', $this->message->getMessageId(1));
    $this->assertEquals('00000126', $this->message->getMessageId(2));
    $this->assertEquals('1.10', $this->message->getRemainingBalance(0));
    $this->assertEquals('1.05', $this->message->getRemainingBalance(1));
    $this->assertEquals('1.00', $this->message->getRemainingBalance(2));
});

test('can iterate over message parts', function () {
    foreach ($this->message as $index => $part) {
        self::fail('should not be able to iterate over empty message');
    }

    @$this->message->setResponse(getResponse('multi'));

    $iterated = false;
    foreach ($this->message as $index => $part) {
        $iterated = true;
        $this->assertEquals('0', $part['status']);
        $this->assertEquals('44123456789', $part['to']);
        $this->assertEquals('23410', $part['network']);
        $this->assertEquals('0.05', $part['message-price']);

        switch ($index) {
            case 0:
                $this->assertEquals('00000124', $part['message-id']);
                $this->assertEquals('1.10', $part['remaining-balance']);
                break;
            case 1:
                $this->assertEquals('00000125', $part['message-id']);
                $this->assertEquals('1.05', $part['remaining-balance']);
                break;
            case 2:
                $this->assertEquals('00000126', $part['message-id']);
                $this->assertEquals('1.00', $part['remaining-balance']);
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
