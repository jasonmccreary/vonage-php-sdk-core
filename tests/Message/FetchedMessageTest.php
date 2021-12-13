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

    $this->assertEquals('ACCEPTD', @$this->message['status']);
    $this->assertEquals('02000000D912945A', @$this->message['message-id']);
    $this->assertEquals('14845551212', @$this->message['to']);
    $this->assertEquals('16105553980', @$this->message['from']);
    $this->assertEquals('test with signature', @$this->message['body']);
    $this->assertEquals('0.00570000', @$this->message['price']);
    $this->assertEquals('2016-05-19 17:44:06', @$this->message['date-received']);
    $this->assertEquals('1', @$this->message['error-code']);
    $this->assertEquals('Unknown', @$this->message['error-code-label']);
    $this->assertEquals('MT', @$this->message['type']);
});

/**
 * @throws Exception
 */
test('can access last message as object', function () {
    $date = new DateTime();
    $date->setDate(2016, 5, 19);
    $date->setTime(17, 44, 06);

    @$this->message->setResponse(getResponse('search-outbound'));

    $this->assertEquals('ACCEPTD', $this->message->getDeliveryStatus());
    $this->assertEquals('02000000D912945A', $this->message->getMessageId());
    $this->assertEquals('14845551212', $this->message->getTo());
    $this->assertEquals('16105553980', $this->message->getFrom());
    $this->assertEquals('test with signature', $this->message->getBody());
    $this->assertEquals('0.00570000', $this->message->getPrice());
    $this->assertEquals($date, $this->message->getDateReceived());
    $this->assertEquals('1', $this->message->getDeliveryError());
    $this->assertEquals('Unknown', $this->message->getDeliveryLabel());
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
