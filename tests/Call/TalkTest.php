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
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Call\Event;
use Vonage\Call\Talk;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use VonageTest\Psr7AssertionTrait;

uses(VonageTestCase::class);
uses(Psr7AssertionTrait::class);


beforeEach(function () {
    $this->id = '3fd4d839-493e-4485-b2a5-ace527aacff3';
    $this->class = Talk::class;

    $this->entity = @new Talk('3fd4d839-493e-4485-b2a5-ace527aacff3');
    $this->new = @new Talk();

    $this->vonageClient = $this->prophesize('Vonage\Client');
    $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

    /** @noinspection PhpParamsInspection */
    $this->entity->setClient($this->vonageClient->reveal());
    /** @noinspection PhpParamsInspection */
    $this->new->setClient($this->vonageClient->reveal());
});

test('has id', function () {
    $this->assertSame($this->id, $this->entity->getId());
});

/**
 * @param $value
 * @param $param
 * @param $setter
 * @param $expected
 */
test('set params', function ($value, $param, $setter, $expected) {
    $this->entity->$setter($value);
    $data = $this->entity->jsonSerialize();

    $this->assertEquals($expected, $data[$param]);
})->with('setterParameters');

/**
 * @param $value
 * @param $param
 */
test('array params', function ($value, $param) {
    $this->entity[$param] = $value;
    $data = $this->entity->jsonSerialize();

    $this->assertEquals($value, $data[$param]);
})->with('setterParameters');

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('put makes request', function () {
    $this->entity->setText('Bingo!');

    $callId = $this->id;
    $entity = $this->entity;

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId, $entity) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/talk', 'PUT', $request);
        $expected = json_decode(json_encode($entity), true);

        $request->getBody()->rewind();
        $body = json_decode($request->getBody()->getContents(), true);
        $request->getBody()->rewind();

        $this->assertEquals($expected, $body);
        return true;
    }))->willReturn(getResponse('talk', 200));

    $event = @$this->entity->put();

    $this->assertInstanceOf(Event::class, $event);
    $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
    $this->assertSame('Talk started', $event['message']);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('put can replace', function () {
    $class = $this->class;

    $entity = @new $class();
    $entity->setText('Ding!');

    $callId = $this->id;

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId, $entity) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/talk', 'PUT', $request);
        $expected = json_decode(json_encode($entity), true);

        $request->getBody()->rewind();
        $body = json_decode($request->getBody()->getContents(), true);
        $request->getBody()->rewind();

        $this->assertEquals($expected, $body);
        return true;
    }))->willReturn(getResponse('talk', 200));

    $event = @$this->entity->put($entity);

    $this->assertInstanceOf(Event::class, $event);
    $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
    $this->assertSame('Talk started', $event['message']);
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('invoke proxies put with argument', function () {
    $object = $this->entity;

    $this->vonageClient->send(Argument::any())->willReturn(getResponse('talk', 200));
    $test = $object();
    $this->assertSame($this->entity, $test);

    $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();

    $class = $this->class;
    $entity = @new $class();
    $entity->setText('Hello!');

    $event = @$object($entity);

    $this->assertInstanceOf(Event::class, $event);
    $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
    $this->assertSame('Talk started', $event['message']);

    $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
});

/**
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws RequestException
 * @throws ServerException
 */
test('delete makes request', function () {
    $callId = $this->id;

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/talk', 'DELETE', $request);
        return true;
    }))->willReturn(getResponse('talk-delete', 200));

    $event = @$this->entity->delete();

    $this->assertInstanceOf(Event::class, $event);
    $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
    $this->assertSame('Talk stopped', $event['message']);
});

// Datasets
dataset('setterParameters', [
    ['something I want to say', 'text', 'setText', 'something I want to say'],
    ['Ivy', 'voice_name', 'setVoiceName', 'Ivy'],
    [0, 'loop', 'setLoop', '0'],
    [1, 'loop', 'setLoop', '1'],
]);

// Helpers
/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
}
