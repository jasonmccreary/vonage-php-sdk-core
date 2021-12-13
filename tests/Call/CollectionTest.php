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
use Vonage\Call\Call;
use Vonage\Call\Collection;
use Vonage\Call\Filter;
use Vonage\Call\Transfer;
use Vonage\Client\APIResource;
use Vonage\Client\Exception as ClientException;
use VonageTest\Psr7AssertionTrait;

uses(VonageTestCase::class);
uses(Psr7AssertionTrait::class);

use function fopen;
use function json_encode;

beforeEach(function () {
    $this->vonageClient = $this->prophesize('Vonage\Client');
    $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

    $this->collection = @new Collection();
    /** @noinspection PhpParamsInspection */
    $this->collection->setClient($this->vonageClient->reveal());
});

/**
 * Collection can be invoked as a method. This allows a fluent inerface from the main client. When invoked with a
 * filter, the collection should use that filter.
 *
 *     $Vonage->calls($filter)
 */
test('invoke with filter', function () {
    $collection = $this->collection;
    $filter = @new Filter();
    $return = @$collection($filter);

    $this->assertSame($collection, $return);
    $this->assertSame($collection->getFilter(), $filter);
});

/**
 * Hydrate is used by the common collection paging.
 */
test('hydrate sets data and client', function () {
    self::markTestIncomplete('Rework this test to make sure hydrate fills correct values');

    /** @var mixed $call */
    $call = $this->prophesize(Call::class);

    $data = ['test' => 'data'];

    $this->collection->hydrateEntity($data, $call->reveal());

    $call->setClient($this->vonageClient->reveal())->shouldHaveBeenCalled();
    $call->jsonUnserialize($data)->shouldHaveBeenCalled();
});

/**
 * Getting an entity from the collection should not fetch it if we use the array interface.
 *
 *
 * @param $payload
 * @param $id
 */
test('array is lazy', function ($payload, $id) {
    //not testing the call resource, just making sure it uses the same client as the collection
    $this->vonageClient->send(Argument::any())->willReturn(getResponse('call'));

    $collection = $this->collection;
    $call = @$collection[$payload];

    $this->assertInstanceOf(Call::class, $call);
    $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();
    $this->assertEquals($id, $call->getId());

    if ($payload instanceof Call) {
        $this->assertSame($payload, $call);
    }

    @$call->get();
    $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
})->with('getCall');

/**
 * Using `get()` should fetch the call data. Will accept both a string id and an object. Must return the same object
 * if that's the input.
 *
 *
 * @param $payload
 * @param $id
 *
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ClientException\Server
 * @throws ClientExceptionInterface
 */
test('get is not lazy', function ($payload, $id) {
    //this generally proxies the call resource, but we're testing the correct request, not the proxy
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
        return true;
    }))->willReturn(getResponse('call'))->shouldBeCalled();

    $call = @$this->collection->get($payload);

    $this->assertInstanceOf(Call::class, $call);
    if ($payload instanceof Call) {
        $this->assertSame($payload, $call);
    }
})->with('getCall');

/**
 *
 * @param $payload
 * @param $method
 */
test('create post call', function ($payload, $method) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);
        return true;
    }))->willReturn(getResponse('created', 201));

    $call = @$this->collection->$method($payload);

    $this->assertInstanceOf(Call::class, $call);
    $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $call->getId());
})->with('postCall');

/**
 *
 * @param $payload
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ClientException\Server
 */
test('create call ncco', function ($payload) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $ncco = [['action' => 'talk', 'text' => 'Hello World']];

        $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);
        $this->assertRequestJsonBodyContains('ncco', $ncco, $request);
        return true;
    }))->willReturn(getResponse('created', 201));

    $call = @$this->collection->create($payload);

    $this->assertInstanceOf(Call::class, $call);
    $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $call->getId());
})->with('postCallNcco');

/**
 *
 * @param $payload
 * @param $method
 */
test('create post call error from v api', function ($payload, $method) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('error_vapi', 400));

    try {
        @$this->collection->$method($payload);

        self::fail('Expected to throw request exception');
    } catch (ClientException\Request $e) {
        $this->assertEquals('Bad Request', $e->getMessage());
    }
})->with('postCall');

/**
 *
 * @param $payload
 * @param $method
 */
test('create post call error from proxy', function ($payload, $method) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('error_proxy', 400));

    try {
        @$this->collection->$method($payload);

        self::fail('Expected to throw request exception');
    } catch (ClientException\Request $e) {
        $this->assertEquals('Unsupported Media Type', $e->getMessage());
    }
})->with('postCall');

/**
 *
 * @param $payload
 * @param $method
 */
test('create post call error unknown format', function ($payload, $method) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);
        return true;
    }))->willReturn(getResponse('error_unknown_format', 400));

    try {
        @$this->collection->$method($payload);

        self::fail('Expected to throw request exception');
    } catch (ClientException\Request $e) {
        $this->assertEquals("Unexpected error", $e->getMessage());
    }
})->with('postCall');

/**
 *
 * @param $expectedId
 * @param $id
 * @param $payload
 *
 * @throws ClientExceptionInterface
 * @throws ClientException\Exception
 * @throws ClientException\Request
 * @throws ClientException\Server
 */
test('put call', function ($expectedId, $id, $payload) {
    //this generally proxies the call resource, but we're testing the correct request, not the proxy
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($expectedId, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $expectedId, 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);
        return true;
    }))->willReturn(getResponse('updated'))->shouldBeCalled();

    $call = @$this->collection->put($payload, $id);
    $this->assertInstanceOf(Call::class, $call);

    if ($id instanceof Call) {
        $this->assertSame($id, $call);
    } else {
        $this->assertEquals($id, $call->getId());
    }
})->with('putCall');

// Datasets
/**
 * Getting a call can use an object or an ID.
 */
dataset('getCall', [
    ['3fd4d839-493e-4485-b2a5-ace527aacff3', '3fd4d839-493e-4485-b2a5-ace527aacff3'],
    [@new Call('3fd4d839-493e-4485-b2a5-ace527aacff3'), '3fd4d839-493e-4485-b2a5-ace527aacff3']
]);

/**
 * Creating a call with an NCCO can take a Call object or a simple array.
 */
dataset('postCallNcco', function () {
    $raw = [
        'to' => [
            [
                'type' => 'phone',
                'number' => '14843331234'
            ]
        ],
        'from' => [
            'type' => 'phone',
            'number' => '14843335555'
        ],
        'ncco' => [
            [
                'action' => 'talk',
                'text' => 'Hello World'
            ]
        ]
    ];

    $call = @new Call();
    @$call->setTo('14843331234')
        ->setFrom('14843335555')
        ->setNcco([
            [
                'action' => 'talk',
                'text' => 'Hello World'
            ]
        ]);

    return [
        'object' => [clone $call],
        'array' => [$raw]
    ];
});

/**
 * Creating a call can take a Call object or a simple array.
 */
dataset('postCall', function () {
    $raw = [
        'to' => [
            [
                'type' => 'phone',
                'number' => '14843331234'
            ]
        ],
        'from' => [
            'type' => 'phone',
            'number' => '14843335555'
        ],
        'answer_url' => ['https://example.com/answer'],
        'event_url' => ['https://example.com/event'],
        'answer_method' => 'POST',
        'event_method' => 'POST'
    ];

    $call = @new Call();
    @$call->setTo('14843331234')
        ->setFrom('14843335555')
        ->setWebhook(@Call::WEBHOOK_ANSWER, 'https://example.com/answer', 'POST')
        ->setWebhook(@Call::WEBHOOK_EVENT, 'https://example.com/event', 'POST');

    return [
        [clone $call, 'create'],
        [clone $call, 'post'],
        [$raw, 'create'],
        [$raw, 'post'],
    ];
});

/**
 * Can update the call with an object or a raw array.
 */
dataset('putCall', function () {
    $id = '1234abcd';
    $payload = [
        'action' => 'transfer',
        'destination' => [
            'type' => 'ncco',
            'url' => ['http://example.com']
        ]
    ];

    $call = @new Call($id);
    $transfer = @new Transfer('http://example.com');

    return [
        [$id, $id, $payload],
        [$id, $call, $payload],
        [$id, $id, $transfer],
        [$id, $call, $transfer]
    ];
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
}
