<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Call\Dtmf;
use Vonage\Call\Event;
use Vonage\Client;
use VonageTest\Psr7AssertionTrait;

uses(Psr7AssertionTrait::class);


beforeEach(function () {
    $this->id = '3fd4d839-493e-4485-b2a5-ace527aacff3';
    $this->class = Dtmf::class;

    $this->entity = @new Dtmf('3fd4d839-493e-4485-b2a5-ace527aacff3');
    $this->new = @new Dtmf();

    $this->vonageClient = $this->prophesize(Client::class);
    $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

    /** @noinspection PhpParamsInspection */
    $this->entity->setClient($this->vonageClient->reveal());
    /** @noinspection PhpParamsInspection */
    $this->new->setClient($this->vonageClient->reveal());
});

test('has id', function () {
    expect($this->entity->getId())->toBe($this->id);
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

    expect($data[$param])->toEqual($expected);
})->with('setterParameters');

/**
 * @param $value
 * @param $param
 */
test('array params', function ($value, $param) {
    $this->entity[$param] = $value;
    $data = $this->entity->jsonSerialize();

    expect($data[$param])->toEqual($value);
})->with('setterParameters');

/**
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws Client\Exception\Server
 * @throws ClientExceptionInterface
 */
test('put makes request', function () {
    $this->entity->setDigits('3119');

    $callId = $this->id;
    $entity = $this->entity;

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId, $entity) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/dtmf', 'PUT', $request);
        $expected = json_decode(json_encode($entity), true);

        $request->getBody()->rewind();
        $body = json_decode($request->getBody()->getContents(), true);
        $request->getBody()->rewind();

        expect($body)->toEqual($expected);
        return true;
    }))->willReturn(getResponse('dtmf', 200));

    $event = @$this->entity->put();

    expect($event)->toBeInstanceOf(Event::class);
    expect($event['uuid'])->toBe('ssf61863-4a51-ef6b-11e1-w6edebcf93bb');
    expect($event['message'])->toBe('DTMF sent');
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws Client\Exception\Server
 */
test('put can replace', function () {
    $class = $this->class;

    $entity = @new $class();
    $entity->setDigits('1234');

    $callId = $this->id;

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($callId, $entity) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/dtmf', 'PUT', $request);
        $expected = json_decode(json_encode($entity), true);

        $request->getBody()->rewind();
        $body = json_decode($request->getBody()->getContents(), true);
        $request->getBody()->rewind();

        expect($body)->toEqual($expected);
        return true;
    }))->willReturn(getResponse('dtmf', 200));

    $event = @$this->entity->put($entity);

    expect($event)->toBeInstanceOf(Event::class);
    expect($event['uuid'])->toBe('ssf61863-4a51-ef6b-11e1-w6edebcf93bb');
    expect($event['message'])->toBe('DTMF sent');
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Request
 * @throws Client\Exception\Server
 */
test('invoke proxies put with argument', function () {
    $object = $this->entity;

    $this->vonageClient->send(Argument::any())->willReturn(getResponse('dtmf', 200));
    $test = $object();
    expect($test)->toBe($this->entity);

    $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();

    $class = $this->class;
    $entity = @new $class();
    $entity->setDigits(1234);

    $event = @$object($entity);

    expect($event)->toBeInstanceOf(Event::class);
    expect($event['uuid'])->toBe('ssf61863-4a51-ef6b-11e1-w6edebcf93bb');
    expect($event['message'])->toBe('DTMF sent');

    $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
});

// Datasets
/**
 * @return string[]
 */
dataset('setterParameters', [
    ['1234', 'digits', 'setDigits', '1234']
]);

// Helpers
/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
}
