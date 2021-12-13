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
use Vonage\Voice\Webhook\Answer;
use Vonage\Voice\Webhook\Error;
use Vonage\Voice\Webhook\Event;
use Vonage\Voice\Webhook\Factory;
use Vonage\Voice\Webhook\Input;
use Vonage\Voice\Webhook\Notification;
use Vonage\Voice\Webhook\Record;
use Vonage\Voice\Webhook\Transfer;

/**
 * @throws Exception
 */
test('can generate started event', function () {
    $request = getRequest('event-post-started');
    $expected = json_decode(getRequest('event-post-started')->getBody()->getContents(), true);
    $event = Factory::createFromRequest($request);

    expect($event)->toBeInstanceOf(Event::class);
    expect($event->getStatus())->toBe($expected['status']);
    expect($event->getFrom())->toBe($expected['from']);
    expect($event->getTo())->toBe($expected['to']);
    expect($event->getUuid())->toBe($expected['uuid']);
    expect($event->getConversationUuid())->toBe($expected['conversation_uuid']);
    expect($event->getDirection())->toBe($expected['direction']);
    expect($event->getTimestamp())->toEqual(new DateTime($expected['timestamp']));
    expect($event->getDuration())->toBeNull();
    expect($event->getPrice())->toBeNull();
});

/**
 * @throws Exception
 */
test('can generate ringing event', function () {
    $request = getRequest('event-post-ringing');
    $expected = json_decode(getRequest('event-post-ringing')->getBody()->getContents(), true);
    $event = Factory::createFromRequest($request);

    expect($event)->toBeInstanceOf(Event::class);
    expect($event->getStatus())->toBe($expected['status']);
    expect($event->getFrom())->toBe($expected['from']);
    expect($event->getTo())->toBe($expected['to']);
    expect($event->getUuid())->toBe($expected['uuid']);
    expect($event->getConversationUuid())->toBe($expected['conversation_uuid']);
    expect($event->getDirection())->toBe($expected['direction']);
    expect($event->getTimestamp())->toEqual(new DateTime($expected['timestamp']));
    expect($event->getDuration())->toBeNull();
    expect($event->getPrice())->toBeNull();
});

/**
 * @throws Exception
 */
test('can generate answered event', function () {
    $request = getRequest('event-post-answered');
    $expected = json_decode(getRequest('event-post-answered')->getBody()->getContents(), true);
    $event = Factory::createFromRequest($request);

    expect($event)->toBeInstanceOf(Event::class);
    expect($event->getStatus())->toBe($expected['status']);
    expect($event->getFrom())->toBe($expected['from']);
    expect($event->getTo())->toBe($expected['to']);
    expect($event->getUuid())->toBe($expected['uuid']);
    expect($event->getConversationUuid())->toBe($expected['conversation_uuid']);
    expect($event->getDirection())->toBe($expected['direction']);
    expect($event->getTimestamp())->toEqual(new DateTime($expected['timestamp']));
    expect($event->getStartTime())->toBeNull();
    expect($event->getRate())->toBeNull();
});

/**
 * @throws Exception
 */
test('can generate completed event', function () {
    $request = getRequest('event-post-completed');
    $expected = json_decode(getRequest('event-post-completed')->getBody()->getContents(), true);
    $event = Factory::createFromRequest($request);

    expect($event)->toBeInstanceOf(Event::class);
    expect($event->getStatus())->toBe($expected['status']);
    expect($event->getFrom())->toBe($expected['from']);
    expect($event->getTo())->toBe($expected['to']);
    expect($event->getUuid())->toBe($expected['uuid']);
    expect($event->getConversationUuid())->toBe($expected['conversation_uuid']);
    expect($event->getDirection())->toBe($expected['direction']);
    expect($event->getTimestamp())->toEqual(new DateTime($expected['timestamp']));
    expect($event->getNetwork())->toBe($expected['network']);
    expect($event->getDuration())->toBe($expected['duration']);
    expect($event->getStartTime())->toEqual(new DateTime($expected['start_time']));
    expect($event->getEndTime())->toEqual(new DateTime($expected['end_time']));
    expect($event->getRate())->toBe($expected['rate']);
    expect($event->getPrice())->toBe($expected['price']);
});

/**
 * @throws Exception
 */
test('can generate transfer webhook', function () {
    $request = getRequest('event-post-transfer');
    $expected = json_decode(getRequest('event-post-transfer')->getBody()->getContents(), true);
    $event = Factory::createFromRequest($request);

    expect($event)->toBeInstanceOf(Transfer::class);
    expect($event->getConversationUuidFrom())->toBe($expected['conversation_uuid_from']);
    expect($event->getConversationUuidTo())->toBe($expected['conversation_uuid_to']);
    expect($event->getUuid())->toBe($expected['uuid']);
    expect($event->getTimestamp())->toEqual(new DateTimeImmutable($expected['timestamp']));
});

test('can generate an answer webhook', function () {
    $request = getRequest('answer-get');
    $expected = getRequest('answer-get')->getQueryParams();

    /** @var Answer $answer */
    $answer = Factory::createFromRequest($request);

    expect($answer)->toBeInstanceOf(Answer::class);
    expect($answer->getConversationUuid())->toBe($expected['conversation_uuid']);
    expect($answer->getUuid())->toBe($expected['uuid']);
    expect($answer->getTo())->toBe($expected['to']);
    expect($answer->getFrom())->toBe($expected['from']);
});

/**
 * @throws Exception
 */
test('can generate a recording webhook', function () {
    $request = getRequest('recording-get');
    $expected = getRequest('recording-get')->getQueryParams();

    /** @var Record $record */
    $record = Factory::createFromRequest($request);

    expect($record)->toBeInstanceOf(Record::class);
    expect($record->getConversationUuid())->toBe($expected['conversation_uuid']);
    expect($record->getEndTime())->toEqual(new DateTimeImmutable($expected['end_time']));
    expect($record->getRecordingUrl())->toBe($expected['recording_url']);
    expect($record->getRecordingUuid())->toBe($expected['recording_uuid']);
    expect($record->getSize())->toBe((int)$expected['size']);
    expect($record->getStartTime())->toEqual(new DateTimeImmutable($expected['start_time']));
    expect($record->getTimestamp())->toEqual(new DateTimeImmutable($expected['timestamp']));
});

/**
 * @throws Exception
 */
test('can generate an error webhook', function () {
    $request = getRequest('error-get');
    $expected = getRequest('error-get')->getQueryParams();

    /** @var Error $error */
    $error = Factory::createFromRequest($request);

    expect($error)->toBeInstanceOf(Error::class);
    expect($error->getConversationUuid())->toBe($expected['conversation_uuid']);
    expect($error->getReason())->toBe($expected['reason']);
    expect($error->getTimestamp())->toEqual(new DateTimeImmutable($expected['timestamp']));
});

/**
 * @throws Exception
 */
test('can generate a notification get webhook', function () {
    $request = getRequest('event-get-notify');
    $expected = getRequest('event-get-notify')->getQueryParams();

    /** @var Notification $notification */
    $notification = Factory::createFromRequest($request);

    expect($notification)->toBeInstanceOf(Notification::class);
    expect($notification->getConversationUuid())->toBe($expected['conversation_uuid']);
    expect($notification->getPayload())->toBe(json_decode($expected['payload'], true));
    expect($notification->getTimestamp())->toEqual(new DateTimeImmutable($expected['timestamp']));
});

/**
 * @throws Exception
 */
test('can generate a notification post webhook', function () {
    $request = getRequest('event-post-notify');
    $expected = json_decode(getRequest('event-post-notify')->getBody()->getContents(), true);

    /** @var Notification $notification */
    $notification = Factory::createFromRequest($request);

    expect($notification)->toBeInstanceOf(Notification::class);
    expect($notification->getConversationUuid())->toBe($expected['conversation_uuid']);
    expect($notification->getPayload())->toBe($expected['payload']);
    expect($notification->getTimestamp())->toEqual(new DateTimeImmutable($expected['timestamp']));
});

/**
 * @throws Exception
 */
test('can generate dtmf input from get webhook', function () {
    $request = getRequest('dtmf-get');
    $expected = getRequest('dtmf-get')->getQueryParams();

    /** @var Input $input */
    $input = Factory::createFromRequest($request);

    expect($input)->toBeInstanceOf(Input::class);
    expect($input->getSpeech())->toBe(json_decode($expected['speech'], true));
    expect($input->getDtmf())->toBe(json_decode($expected['dtmf'], true));
    expect($input->getFrom())->toBe($expected['from']);
    expect($input->getTo())->toBe($expected['to']);
    expect($input->getUuid())->toBe($expected['uuid']);
    expect($input->getConversationUuid())->toBe($expected['conversation_uuid']);
    expect($input->getTimestamp())->toEqual(new DateTimeImmutable($expected['timestamp']));
});

/**
 * @throws Exception
 */
test('can generate dtmf input from post webhook', function () {
    $request = getRequest('dtmf-post');
    $expected = json_decode(getRequest('dtmf-post')->getBody()->getContents(), true);

    /** @var Input $input */
    $input = Factory::createFromRequest($request);

    expect($input)->toBeInstanceOf(Input::class);
    expect($input->getSpeech())->toBe($expected['speech']);
    expect($input->getDtmf())->toBe($expected['dtmf']);
    expect($input->getFrom())->toBe($expected['from']);
    expect($input->getTo())->toBe($expected['to']);
    expect($input->getUuid())->toBe($expected['uuid']);
    expect($input->getConversationUuid())->toBe($expected['conversation_uuid']);
    expect($input->getTimestamp())->toEqual(new DateTimeImmutable($expected['timestamp']));
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

    expect($event)->toBeInstanceOf(Event::class);
    expect($event->getDetail())->toBe($expected['detail']);
});

test('event without detail is deserialized properly', function () {
    $request = getRequest('event-post-ringing');
    $expected = json_decode(getRequest('event-post-ringing')->getBody()->getContents(), true);

    /** @var Event $event */
    $event = Factory::createFromRequest($request);

    expect($event)->toBeInstanceOf(Event::class);
    expect($event->getDetail())->toBeNull();
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
