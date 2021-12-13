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
    $this->message = new Message('02000000D912945A');
});

afterEach(function () {
    $this->message = null;
});

test('can access last message as array', function () {
    @$this->message->setResponse(getResponse('search-outbound'));

    expect(@$this->message['status'])->toEqual('ACCEPTD');
    expect(@$this->message['message-id'])->toEqual('02000000D912945A');
    expect(@$this->message['to'])->toEqual('14845551212');
    expect(@$this->message['from'])->toEqual('16105553980');
    expect(@$this->message['body'])->toEqual('test with signature');
    expect(@$this->message['price'])->toEqual('0.00570000');
    expect(@$this->message['date-received'])->toEqual('2016-05-19 17:44:06');
    expect(@$this->message['error-code'])->toEqual('1');
    expect(@$this->message['error-code-label'])->toEqual('Unknown');
    expect(@$this->message['type'])->toEqual('MT');
});

/**
 * @throws Exception
 */
test('can access last message as object', function () {
    $date = new DateTime();
    $date->setDate(2016, 5, 19);
    $date->setTime(17, 44, 06);

    @$this->message->setResponse(getResponse('search-outbound'));

    expect($this->message->getDeliveryStatus())->toEqual('ACCEPTD');
    expect($this->message->getMessageId())->toEqual('02000000D912945A');
    expect($this->message->getTo())->toEqual('14845551212');
    expect($this->message->getFrom())->toEqual('16105553980');
    expect($this->message->getBody())->toEqual('test with signature');
    expect($this->message->getPrice())->toEqual('0.00570000');
    expect($this->message->getDateReceived())->toEqual($date);
    expect($this->message->getDeliveryError())->toEqual('1');
    expect($this->message->getDeliveryLabel())->toEqual('Unknown');
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     */
function getResponse(string $type = 'success'): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
}
