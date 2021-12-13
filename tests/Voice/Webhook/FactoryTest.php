<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Request\Serializer;
use Laminas\Diactoros\ServerRequest;
use VonageTest\VonageTestCase;
use Vonage\Voice\Webhook\Answer;
use Vonage\Voice\Webhook\Error;
use Vonage\Voice\Webhook\Event;
use Vonage\Voice\Webhook\Factory;
use Vonage\Voice\Webhook\Input;
use Vonage\Voice\Webhook\Notification;
use Vonage\Voice\Webhook\Record;
use Vonage\Voice\Webhook\Transfer;

uses(VonageTestCase::class);


/**
 * @throws Exception
 */
test('can generate started event', function () {
    $request = getRequest('event-post-started');
    $expected = json_decode(getRequest('event-post-started')->getBody()->getContents(), true);
    $event = Factory::createFromRequest($request);

    $this->assertInstanceOf(Event::class, $event);
    $this->assertSame($expected['status'], $event->getStatus());
    $this->assertSame($expected['from'], $event->getFrom());
    $this->assertSame($expected['to'], $event->getTo());
    $this->assertSame($expected['uuid'], $event->getUuid());
    $this->assertSame($expected['conversation_uuid'], $event->getConversationUuid());
    $this->assertSame($expected['direction'], $event->getDirection());
    $this->assertEquals(new DateTime($expected['timestamp']), $event->getTimestamp());
    $this->assertNull($event->getDuration());
    $this->assertNull($event->getPrice());
});

/**
 * @throws Exception
 */
test('can generate ringing event', function () {
    $request = getRequest('event-post-ringing');
    $expected = json_decode(getRequest('event-post-ringing')->getBody()->getContents(), true);
    $event = Factory::createFromRequest($request);

    $this->assertInstanceOf(Event::class, $event);
    $this->assertSame($expected['status'], $event->getStatus());
    $this->assertSame($expected['from'], $event->getFrom());
    $this->assertSame($expected['to'], $event->getTo());
    $this->assertSame($expected['uuid'], $event->getUuid());
    $this->assertSame($expected['conversation_uuid'], $event->getConversationUuid());
    $this->assertSame($expected['direction'], $event->getDirection());
    $this->assertEquals(new DateTime($expected['timestamp']), $event->getTimestamp());
    $this->assertNull($event->getDuration());
    $this->assertNull($event->getPrice());
});

/**
 * @throws Exception
 */
test('can generate answered event', function () {
    $request = getRequest('event-post-answered');
    $expected = json_decode(getRequest('event-post-answered')->getBody()->getContents(), true);
    $event = Factory::createFromRequest($request);

    $this->assertInstanceOf(Event::class, $event);
    $this->assertSame($expected['status'], $event->getStatus());
    $this->assertSame($expected['from'], $event->getFrom());
    $this->assertSame($expected['to'], $event->getTo());
    $this->assertSame($expected['uuid'], $event->getUuid());
    $this->assertSame($expected['conversation_uuid'], $event->getConversationUuid());
    $this->assertSame($expected['direction'], $event->getDirection());
    $this->assertEquals(new DateTime($expected['timestamp']), $event->getTimestamp());
    $this->assertNull($event->getStartTime());
    $this->assertNull($event->getRate());
});

/**
 * @throws Exception
 */
test('can generate completed event', function () {
    $request = getRequest('event-post-completed');
    $expected = json_decode(getRequest('event-post-completed')->getBody()->getContents(), true);
    $event = Factory::createFromRequest($request);

    $this->assertInstanceOf(Event::class, $event);
    $this->assertSame($expected['status'], $event->getStatus());
    $this->assertSame($expected['from'], $event->getFrom());
    $this->assertSame($expected['to'], $event->getTo());
    $this->assertSame($expected['uuid'], $event->getUuid());
    $this->assertSame($expected['conversation_uuid'], $event->getConversationUuid());
    $this->assertSame($expected['direction'], $event->getDirection());
    $this->assertEquals(new DateTime($expected['timestamp']), $event->getTimestamp());
    $this->assertSame($expected['network'], $event->getNetwork());
    $this->assertSame($expected['duration'], $event->getDuration());
    $this->assertEquals(new DateTime($expected['start_time']), $event->getStartTime());
    $this->assertEquals(new DateTime($expected['end_time']), $event->getEndTime());
    $this->assertSame($expected['rate'], $event->getRate());
    $this->assertSame($expected['price'], $event->getPrice());
});

/**
 * @throws Exception
 */
test('can generate transfer webhook', function () {
    $request = getRequest('event-post-transfer');
    $expected = json_decode(getRequest('event-post-transfer')->getBody()->getContents(), true);
    $event = Factory::createFromRequest($request);

    $this->assertInstanceOf(Transfer::class, $event);
    $this->assertSame($expected['conversation_uuid_from'], $event->getConversationUuidFrom());
    $this->assertSame($expected['conversation_uuid_to'], $event->getConversationUuidTo());
    $this->assertSame($expected['uuid'], $event->getUuid());
    $this->assertEquals(new DateTimeImmutable($expected['timestamp']), $event->getTimestamp());
});

test('can generate an answer webhook', function () {
    $request = getRequest('answer-get');
    $expected = getRequest('answer-get')->getQueryParams();

    /** @var Answer $answer */
    $answer = Factory::createFromRequest($request);

    $this->assertInstanceOf(Answer::class, $answer);
    $this->assertSame($expected['conversation_uuid'], $answer->getConversationUuid());
    $this->assertSame($expected['uuid'], $answer->getUuid());
    $this->assertSame($expected['to'], $answer->getTo());
    $this->assertSame($expected['from'], $answer->getFrom());
});

/**
 * @throws Exception
 */
test('can generate a recording webhook', function () {
    $request = getRequest('recording-get');
    $expected = getRequest('recording-get')->getQueryParams();

    /** @var Record $record */
    $record = Factory::createFromRequest($request);

    $this->assertInstanceOf(Record::class, $record);
    $this->assertSame($expected['conversation_uuid'], $record->getConversationUuid());
    $this->assertEquals(new DateTimeImmutable($expected['end_time']), $record->getEndTime());
    $this->assertSame($expected['recording_url'], $record->getRecordingUrl());
    $this->assertSame($expected['recording_uuid'], $record->getRecordingUuid());
    $this->assertSame((int)$expected['size'], $record->getSize());
    $this->assertEquals(new DateTimeImmutable($expected['start_time']), $record->getStartTime());
    $this->assertEquals(new DateTimeImmutable($expected['timestamp']), $record->getTimestamp());
});

/**
 * @throws Exception
 */
test('can generate an error webhook', function () {
    $request = getRequest('error-get');
    $expected = getRequest('error-get')->getQueryParams();

    /** @var Error $error */
    $error = Factory::createFromRequest($request);

    $this->assertInstanceOf(Error::class, $error);
    $this->assertSame($expected['conversation_uuid'], $error->getConversationUuid());
    $this->assertSame($expected['reason'], $error->getReason());
    $this->assertEquals(new DateTimeImmutable($expected['timestamp']), $error->getTimestamp());
});

/**
 * @throws Exception
 */
test('can generate a notification get webhook', function () {
    $request = getRequest('event-get-notify');
    $expected = getRequest('event-get-notify')->getQueryParams();

    /** @var Notification $notification */
    $notification = Factory::createFromRequest($request);

    $this->assertInstanceOf(Notification::class, $notification);
    $this->assertSame($expected['conversation_uuid'], $notification->getConversationUuid());
    $this->assertSame(json_decode($expected['payload'], true), $notification->getPayload());
    $this->assertEquals(new DateTimeImmutable($expected['timestamp']), $notification->getTimestamp());
});

/**
 * @throws Exception
 */
test('can generate a notification post webhook', function () {
    $request = getRequest('event-post-notify');
    $expected = json_decode(getRequest('event-post-notify')->getBody()->getContents(), true);

    /** @var Notification $notification */
    $notification = Factory::createFromRequest($request);

    $this->assertInstanceOf(Notification::class, $notification);
    $this->assertSame($expected['conversation_uuid'], $notification->getConversationUuid());
    $this->assertSame($expected['payload'], $notification->getPayload());
    $this->assertEquals(new DateTimeImmutable($expected['timestamp']), $notification->getTimestamp());
});

/**
 * @throws Exception
 */
test('can generate dtmf input from get webhook', function () {
    $request = getRequest('dtmf-get');
    $expected = getRequest('dtmf-get')->getQueryParams();

    /** @var Input $input */
    $input = Factory::createFromRequest($request);

    $this->assertInstanceOf(Input::class, $input);
    $this->assertSame(json_decode($expected['speech'], true), $input->getSpeech());
    $this->assertSame(json_decode($expected['dtmf'], true), $input->getDtmf());
    $this->assertSame($expected['from'], $input->getFrom());
    $this->assertSame($expected['to'], $input->getTo());
    $this->assertSame($expected['uuid'], $input->getUuid());
    $this->assertSame($expected['conversation_uuid'], $input->getConversationUuid());
    $this->assertEquals(new DateTimeImmutable($expected['timestamp']), $input->getTimestamp());
});

/**
 * @throws Exception
 */
test('can generate dtmf input from post webhook', function () {
    $request = getRequest('dtmf-post');
    $expected = json_decode(getRequest('dtmf-post')->getBody()->getContents(), true);

    /** @var Input $input */
    $input = Factory::createFromRequest($request);

    $this->assertInstanceOf(Input::class, $input);
    $this->assertSame($expected['speech'], $input->getSpeech());
    $this->assertSame($expected['dtmf'], $input->getDtmf());
    $this->assertSame($expected['from'], $input->getFrom());
    $this->assertSame($expected['to'], $input->getTo());
    $this->assertSame($expected['uuid'], $input->getUuid());
    $this->assertSame($expected['conversation_uuid'], $input->getConversationUuid());
    $this->assertEquals(new DateTimeImmutable($expected['timestamp']), $input->getTimestamp());
});

/**
 * @throws Exception
 */
test('throws exception on unknown webhook data', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Unable to detect incoming webhook type');

    Factory::createFromArray(['foo' => 'bar']);
});

test('event with detail is deserialized properly', function () {
    $request = getRequest('event-post-failed');
    $expected = json_decode(getRequest('event-post-failed')->getBody()->getContents(), true);

    /** @var Event $event */
    $event = Factory::createFromRequest($request);

    $this->assertInstanceOf(Event::class, $event);
    $this->assertSame($expected['detail'], $event->getDetail());
});

test('event without detail is deserialized properly', function () {
    $request = getRequest('event-post-ringing');
    $expected = json_decode(getRequest('event-post-ringing')->getBody()->getContents(), true);

    /** @var Event $event */
    $event = Factory::createFromRequest($request);

    $this->assertInstanceOf(Event::class, $event);
    $this->assertNull($event->getDetail());
});

// Helpers
function getRequest(string $requestName): ServerRequest
{
    $text = file_get_contents(__DIR__ . '/../requests/' . $requestName . '.txt');
    $request = Serializer::fromString($text);

    parse_str($request->getUri()->getQuery(), $query);

    return new ServerRequest(
        [],
        [],
        $request->getHeader('Host')[0],
        $request->getMethod(),
        $request->getBody(),
        $request->getHeaders(),
        [],
        $query
    );
}
