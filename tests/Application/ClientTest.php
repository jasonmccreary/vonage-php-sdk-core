<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use VonageTest\VonageTestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Application\Application;
use Vonage\Application\Client as ApplicationClient;
use Vonage\Application\Filter;
use Vonage\Application\MessagesConfig;
use Vonage\Application\RtcConfig;
use Vonage\Application\VoiceConfig;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Entity\Filter\EmptyFilter;
use VonageTest\Psr7AssertionTrait;

uses(Psr7AssertionTrait::class);


beforeEach(function () {
    $this->vonageClient = $this->prophesize(Client::class);
    $this->vonageClient->getApiUrl()->willReturn('http://api.nexmo.com');

    $this->applicationClient = new ApplicationClient();
    /** @noinspection PhpParamsInspection */
    $this->applicationClient->setClient($this->vonageClient->reveal());
});

test('size exception', function () {
    $this->expectException('RuntimeException');
    $this->applicationClient->getSize();
});

/**
 * @throws ClientException
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Request
 * @throws ServerException
 */
test('set filter', function () {
    $filter = new Filter(new DateTime('yesterday'), new DateTime('tomorrow'));

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($filter) {
        expect($request->getUri()->getPath())->toEqual('/v2/applications');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('GET');

        foreach ($filter->getQuery() as $key => $value) {
            $this->assertRequestQueryContains($key, $value, $request);
        }

        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('list'));

    expect($this->applicationClient->getFilter())->toBeInstanceOf(EmptyFilter::class);
    expect($this->applicationClient->setFilter($filter))->toBe($this->applicationClient);
    expect($this->applicationClient->getFilter())->toBe($filter);

    $this->applicationClient->rewind();
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Request
 * @throws ClientException
 * @throws ServerException
 */
test('set page', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/v2/applications');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains('page_index', '1', $request);
        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('list'));

    expect($this->applicationClient->setPage(1))->toBe($this->applicationClient);
    expect($this->applicationClient->getPage())->toEqual(1);

    $this->applicationClient->rewind();
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Request
 * @throws ClientException
 * @throws ServerException
 */
test('set size', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/v2/applications');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        $this->assertRequestQueryContains('page_size', '5', $request);
        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('list'));

    expect($this->applicationClient->setSize(5))->toBe($this->applicationClient);
    expect($this->applicationClient->getSize())->toEqual(5);

    $this->applicationClient->rewind();
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Request
 * @throws ClientException
 * @throws ServerException
 */
test('iteration properties', function () {
    $this->vonageClient->send(Argument::type(RequestInterface::class))
        ->shouldBeCalledTimes(1)
        ->willReturn(getResponse('list'));

    expect($this->applicationClient->count())->toEqual(7);
    expect($this->applicationClient)->toHaveCount(7);
    expect($this->applicationClient->getPage())->toEqual(2);
    expect($this->applicationClient->getSize())->toEqual(3);
});

test('iterate pages', function () {
    $page = getResponse('list');
    $last = getResponse('last');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        //a bit hacky here
        static $last;
        if (is_null($last)) { //first call
            $last = $request;
        }

        expect($request->getUri()->getPath())->toEqual('/v2/applications');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('GET');

        if ($last !== $request) { //second call
            $this->assertRequestQueryContains('page_size', '3', $request);
            $this->assertRequestQueryContains('page_index', '3', $request);
        }

        return true;
    }))->shouldBeCalledTimes(2)->willReturn($page, $last);

    foreach ($this->applicationClient as $id => $application) {
        expect($application)->toBeInstanceOf(Application::class);
        expect($id)->toBe($application->getId());
    }
});

test('can iterate client', function () {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        expect($request->getUri()->getPath())->toEqual('/v2/applications');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        return true;
    }))->shouldBeCalledTimes(1)->willReturn(getResponse('list'));

    expect($this->applicationClient)->toBeInstanceOf('Iterator');

    $application = $id = null;

    /** @noinspection LoopWhichDoesNotLoopInspection */
    foreach ($this->applicationClient as $id => $application) {
        break;
    }

    expect(isset($application))->toBeTrue();
    expect($application)->toBeInstanceOf(Application::class);
    expect($id)->toBe($application->getId());
});

/**
 *
 * @param $payload
 * @param $id
 *
 * @throws ClientExceptionInterface
 * @throws ClientException
 * @throws Exception
 */
test('get application', function ($payload, $id) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        expect($request->getUri()->getPath())->toEqual('/v2/applications/' . $id);
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('GET');
        return true;
    }))->willReturn(getResponse());

    $application = @$this->applicationClient->get($payload);
    $expectedData = json_decode(getResponse()->getBody()->getContents(), true);

    expect($application)->toBeInstanceOf(Application::class);
    expect($application->getId())->toBe($expectedData['id']);
    expect($application->getName())->toBe($expectedData['name']);
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['answer_url']['address'],
        $application->getVoiceConfig()->getWebhook('answer_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['answer_url']['http_method'],
        $application->getVoiceConfig()->getWebhook('answer_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['event_url']['address'],
        $application->getVoiceConfig()->getWebhook('event_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['event_url']['http_method'],
        $application->getVoiceConfig()->getWebhook('event_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['inbound_url']['address'],
        $application->getMessagesConfig()->getWebhook('inbound_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['inbound_url']['http_method'],
        $application->getMessagesConfig()->getWebhook('inbound_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['status_url']['address'],
        $application->getMessagesConfig()->getWebhook('status_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['status_url']['http_method'],
        $application->getMessagesConfig()->getWebhook('status_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
        $application->getRtcConfig()->getWebhook('event_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
        $application->getRtcConfig()->getWebhook('event_url')->getUrl()
    );

    self::markTestIncomplete('Remove error suppression when object passing has been removed');
})->with('getApplication');

/**
 *
 * @param $payload
 * @param $method
 * @param $id
 * @param $expectedId
 */
test('update application', function ($payload, $method, $id, $expectedId) {
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($expectedId) {
        expect($request->getUri()->getPath())->toEqual('/v2/applications/' . $expectedId);
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('PUT');

        $this->assertRequestJsonBodyContains('name', 'My Application', $request);

        // And check all other capabilities
        $capabilities = [
            'voice' => [
                'webhooks' => [
                    'answer_url' => [
                        'address' => 'https://example.com/webhooks/answer',
                        'http_method' => null

                    ],
                    'event_url' => [
                        'address' => 'https://example.com/webhooks/event',
                        'http_method' => null
                    ]
                ]
            ],
            'rtc' => [
                'webhooks' => [
                    'event_url' => [
                        'address' => 'https://example.com/webhooks/event',
                        'http_method' => null
                    ]
                ]
            ],
        ];
        $this->assertRequestJsonBodyContains('capabilities', $capabilities, $request);

        return true;
    }))->willReturn(getResponse());

    if ($id) {
        $application = @$this->applicationClient->$method($payload, $id);
    } else {
        $application = @$this->applicationClient->$method($payload);
    }

    $expectedData = json_decode(getResponse()->getBody()->getContents(), true);

    expect($application)->toBeInstanceOf(Application::class);
    expect($application->getId())->toBe($expectedData['id']);
    expect($application->getName())->toBe($expectedData['name']);
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['answer_url']['address'],
        $application->getVoiceConfig()->getWebhook('answer_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['answer_url']['http_method'],
        $application->getVoiceConfig()->getWebhook('answer_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['event_url']['address'],
        $application->getVoiceConfig()->getWebhook('event_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['event_url']['http_method'],
        $application->getVoiceConfig()->getWebhook('event_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['inbound_url']['address'],
        $application->getMessagesConfig()->getWebhook('inbound_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['inbound_url']['http_method'],
        $application->getMessagesConfig()->getWebhook('inbound_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['status_url']['address'],
        $application->getMessagesConfig()->getWebhook('status_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['status_url']['http_method'],
        $application->getMessagesConfig()->getWebhook('status_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
        $application->getRtcConfig()->getWebhook('event_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
        $application->getRtcConfig()->getWebhook('event_url')->getUrl()
    );

    @self::markTestIncomplete("Remove error suppression when object passing has been removed");
    @self::markTestIncomplete(
        "Rework this whole test, because it uses stock responses it's impossible to test in its current form"
    );
})->with('updateApplication');

/**
 *
 * @param $payload
 * @param $id
 *
 * @throws ClientExceptionInterface
 * @throws ClientException
 */
test('delete application', function ($payload, $id) {
    $this->vonageClient->send(Argument::that(function (Request $request) use ($id) {
        expect($request->getUri()->getPath())->toEqual('/v2/applications/' . $id);
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('DELETE');
        return true;
    }))->willReturn(new Response('php://memory', 204));

    expect(@$this->applicationClient->delete($payload))->toBeTrue();
})->with('deleteApplication');

/**
 *
 * @param $method
 * @param $response
 * @param $code
 */
test('throws exception', function ($method, $response, $code) {
    $response = getResponse($response, $code);
    $this->vonageClient->send(Argument::type(RequestInterface::class))->willReturn($response);
    $application = new Application('78d335fa323d01149c3dd6f0d48968cf');

    try {
        @$this->applicationClient->$method($application);

        self::fail('did not throw exception');
    } catch (ClientException $e) {
        $response->getBody()->rewind();
        $data = json_decode($response->getBody()->getContents(), true);
        $class = substr((string)$code, 0, 1);

        $msg = $data['title'];
        if ($data['detail']) {
            $msg .= ': ' . $data['detail'] . '. See ' . $data['type'] . ' for more information';
        }

        switch ($class) {
            case '4':
                expect($e)->toBeInstanceOf(Client\Exception\Request::class);
                expect($e->getMessage())->toEqual($msg);
                expect($e->getCode())->toEqual($code);
                break;
            case '5':
                expect($e)->toBeInstanceOf(ServerException::class);
                expect($e->getMessage())->toEqual($msg);
                expect($e->getCode())->toEqual($code);
                break;
            default:
                expect($e)->toBeInstanceOf(ClientException::class);
                expect($e->getMessage())->toEqual('Unexpected HTTP Status Code');
                break;
        }
    }

    self::markTestIncomplete('Break this test up, it is doing way too much');
})->with('exceptions');

/**
 *
 * @param $payload
 * @param $method
 */
test('create application', function ($payload, $method) {
    $this->vonageClient->send(Argument::that(function (Request $request) {
        expect($request->getUri()->getPath())->toEqual('/v2/applications');
        expect($request->getUri()->getHost())->toEqual('api.nexmo.com');
        expect($request->getMethod())->toEqual('POST');

        $this->assertRequestJsonBodyContains('name', 'My Application', $request);

        // Check for VBC as an object explicitly
        $request->getBody()->rewind();
        expect($request->getBody()->getContents())->toContain('"vbc":{}');

        // And check all other capabilities
        $capabilities = [
            'voice' => [
                'webhooks' => [
                    'answer_url' => [
                        'address' => 'https://example.com/webhooks/answer',
                        'http_method' => 'GET'

                    ],
                    'event_url' => [
                        'address' => 'https://example.com/webhooks/event',
                        'http_method' => 'POST'
                    ]
                ]
            ],
            'messages' => [
                'webhooks' => [
                    'inbound_url' => [
                        'address' => 'https://example.com/webhooks/inbound',
                        'http_method' => 'POST'

                    ],
                    'status_url' => [
                        'address' => 'https://example.com/webhooks/status',
                        'http_method' => 'POST'
                    ]
                ]
            ],
            'rtc' => [
                'webhooks' => [
                    'event_url' => [
                        'address' => 'https://example.com/webhooks/event',
                        'http_method' => 'POST',
                    ],
                ]
            ],
            'vbc' => []
        ];
        $this->assertRequestJsonBodyContains('capabilities', $capabilities, $request);

        // And the public key
        $keys = [
            'public_key' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjx" .
                "u33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xs" .
                "O\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n",
        ];
        $this->assertRequestJsonBodyContains('keys', $keys, $request);
        return true;
    }))->willReturn(getResponse('success', 201));

    $application = @$this->applicationClient->$method($payload);

    $expectedData = json_decode(getResponse()->getBody()->getContents(), true);
    expect($application)->toBeInstanceOf(Application::class);
    expect($application->getId())->toBe($expectedData['id']);
    expect($application->getName())->toBe($expectedData['name']);
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['answer_url']['address'],
        $application->getVoiceConfig()->getWebhook('answer_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['answer_url']['http_method'],
        $application->getVoiceConfig()->getWebhook('answer_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['event_url']['address'],
        $application->getVoiceConfig()->getWebhook('event_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['voice']['webhooks']['event_url']['http_method'],
        $application->getVoiceConfig()->getWebhook('event_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['inbound_url']['address'],
        $application->getMessagesConfig()->getWebhook('inbound_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['inbound_url']['http_method'],
        $application->getMessagesConfig()->getWebhook('inbound_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['status_url']['address'],
        $application->getMessagesConfig()->getWebhook('status_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['messages']['webhooks']['status_url']['http_method'],
        $application->getMessagesConfig()->getWebhook('status_url')->getMethod()
    );
    $this->assertSame(
        $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
        $application->getRtcConfig()->getWebhook('event_url')->getUrl()
    );
    $this->assertSame(
        $expectedData['capabilities']['rtc']['webhooks']['event_url']['address'],
        $application->getRtcConfig()->getWebhook('event_url')->getUrl()
    );
})->with('createApplication');

// Datasets
dataset('getApplication', [
    ['78d335fa323d01149c3dd6f0d48968cf', '78d335fa323d01149c3dd6f0d48968cf'],
    [new Application('78d335fa323d01149c3dd6f0d48968cf'), '78d335fa323d01149c3dd6f0d48968cf']
]);

/**
 * @throws Exception
 *
 * @return array[]
 */
dataset('updateApplication', function () {
    $id = '1a20a124-1775-412b-b623-e6985f4aace0';
    $copy = '1a20a124-1775-412b-4444-e6985f4aace0';
    $existing = new Application($id);
    $existing->setName('My Application');
    @$existing->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/webhooks/answer');
    @$existing->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/webhooks/event');
    @$existing->getRtcConfig()->setWebhook(RtcConfig::EVENT, 'https://example.com/webhooks/event');

    $new = new Application();
    $new->setName('My Application');
    @$new->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/webhooks/answer');
    @$new->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/webhooks/event');
    @$new->getRtcConfig()->setWebhook(RtcConfig::EVENT, 'https://example.com/webhooks/event');

    $raw = [
        'name' => 'My Application',
        'answer_url' => 'https://example.com/webhooks/answer',
        'event_url' => 'https://example.com/webhooks/event'
    ];

    return [
        //can send an application to update it
        [clone $existing, 'update', null, $id],
        //can send raw array and id
        [$raw, 'update', $id, $id],
        //one application overwrites another if id provided
        [clone $existing, 'update', $copy, $copy],
    ];
});

dataset('deleteApplication', [
    [new Application('abcd1234'), 'abcd1234'],
    ['abcd1234', 'abcd1234'],
]);

/**
 * @return string[]
 */
dataset('exceptions', function () {
    //todo: add server error
    return [
        //post / create are aliases
        ['update', 'bad', 400],
        ['update', 'unauthorized', 401],
        ['create', 'bad', 400],
        ['create', 'unauthorized', 401],
        ['delete', 'bad', 400],
        ['delete', 'unauthorized', 401],
    ];
});

/**
 * @throws Exception
 *
 * @return array[]
 */
dataset('createApplication', function () {
    $application = new Application();
    $application->setName('My Application');
    @$application->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/webhooks/answer', 'GET');
    @$application->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/webhooks/event', 'POST');
    @$application->getMessagesConfig()->setWebhook(
        MessagesConfig::STATUS,
        'https://example.com/webhooks/status',
        'POST'
    );
    @$application->getMessagesConfig()->setWebhook(
        MessagesConfig::INBOUND,
        'https://example.com/webhooks/inbound',
        'POST'
    );
    @$application->getRtcConfig()->setWebhook(RtcConfig::EVENT, 'https://example.com/webhooks/event', 'POST');
    $application->setPublicKey("-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSL" .
        "cjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOU" .
        "NhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n");
    $application->getVbcConfig()->enable();

    $rawV1 = [
        'name' => 'My Application',
        'answer_url' => 'https://example.com/webhooks/answer',
        'answer_method' => 'GET',
        'event_url' => 'https://example.com/webhooks/event',
        'event_method' => 'POST',
        'status_url' => 'https://example.com/webhooks/status',
        'status_method' => 'POST',
        'inbound_url' => 'https://example.com/webhooks/inbound',
        'inbound_method' => 'POST',
        'vbc' => true,
        'public_key' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G" .
            "\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhP" .
            "x2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n"
    ];

    $rawV2 = [
        'name' => 'My Application',
        'keys' => [
            'public_key' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjx" .
                "u33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xs" .
                "O\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n"
        ],
        'capabilities' => [
            'voice' => [
                'webhooks' => [
                    'answer_url' => [
                        'address' => 'https://example.com/webhooks/answer',
                        'http_method' => 'GET',
                    ],
                    'event_url' => [
                        'address' => 'https://example.com/webhooks/event',
                        'http_method' => 'POST',
                    ],
                ]
            ],
            'messages' => [
                'webhooks' => [
                    'inbound_url' => [
                        'address' => 'https://example.com/webhooks/inbound',
                        'http_method' => 'POST'

                    ],
                    'status_url' => [
                        'address' => 'https://example.com/webhooks/status',
                        'http_method' => 'POST'
                    ]
                ]
            ],
            'rtc' => [
                'webhooks' => [
                    'event_url' => [
                        'address' => 'https://example.com/webhooks/event',
                        'http_method' => 'POST',
                    ],
                ]
            ],
            'vbc' => []
        ]
    ];

    return [
        'createApplication' => [clone $application, 'create'],
        'postApplication' => [clone $application, 'post'],
        'createRawV1' => [$rawV1, 'create'],
        'postRawV1' => [$rawV1, 'post'],
        'createRawV2' => [$rawV2, 'create'],
        'postRawV2' => [$rawV2, 'post'],
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