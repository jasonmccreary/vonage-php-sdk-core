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
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Request as RequestException;
use VonageTest\Psr7AssertionTrait;
use Vonage\Voice\CallAction;
use Vonage\Voice\Client as VoiceClient;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\Filter\VoiceFilter;
use Vonage\Voice\NCCO\Action\Talk;
use Vonage\Voice\NCCO\NCCO;
use Vonage\Voice\OutboundCall;
use Vonage\Voice\Webhook;

uses(VonageTestCase::class);
uses(Psr7AssertionTrait::class);

use function fopen;
use function json_decode;
use function json_encode;

beforeEach(function () {
    $this->vonageClient = $this->prophesize(Client::class);
    $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

    /** @noinspection PhpParamsInspection */
    $this->api = (new APIResource())
        ->setBaseUri('/v1/calls')
        ->setCollectionName('calls')
        ->setClient($this->vonageClient->reveal());

    $this->voiceClient = new VoiceClient($this->api);
});

/**
 * @throws Client\Exception\Exception
 * @throws ClientExceptionInterface
 */
test('can create outbound call', function () {
    $payload = [
        'to' => [
            [
                'type' => 'phone',
                'number' => '15555555555'
            ]
        ],
        'from' => [
            'type' => 'phone',
            'number' => '16666666666'
        ],
        'answer_url' => ['http://domain.test/answer'],
        'answer_method' => 'POST',
        'event_url' => ['http://domain.test/event'],
        'event_method' => 'POST',
        'machine_detection' => 'hangup',
        'length_timer' => '7200',
        'ringing_timer' => '60'
    ];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('create-outbound-call-success', 201));

    $outboundCall = (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
        ->setEventWebhook(new Webhook('http://domain.test/event'))
        ->setAnswerWebhook(new Webhook('http://domain.test/answer'))
        ->setRingingTimer((int)$payload['ringing_timer'])
        ->setLengthTimer((int)$payload['length_timer'])
        ->setMachineDetection(OutboundCall::MACHINE_HANGUP);
    $callData = $this->voiceClient->createOutboundCall($outboundCall);

    $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $callData->getUuid());
    $this->assertEquals('started', $callData->getStatus());
    $this->assertEquals('outbound', $callData->getDirection());
    $this->assertEquals('2541d01c-253e-48be-a8e0-da4bbe4c3722', $callData->getConversationUuid());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can create outbound call with n c c o', function () {
    $payload = [
        'to' => [
            [
                'type' => 'phone',
                'number' => '15555555555'
            ]
        ],
        'from' => [
            'type' => 'phone',
            'number' => '16666666666'
        ],
        'event_url' => ['http://domain.test/event'],
        'event_method' => 'POST',
        'ncco' => [
            [
                'action' => 'talk',
                'text' => 'Thank you for trying Vonage',
            ]
        ],
        'length_timer' => '7200',
        'ringing_timer' => '60'
    ];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('create-outbound-call-success', 201));

    $outboundCall = (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
        ->setEventWebhook(new Webhook('http://domain.test/event'))
        ->setNCCO((new NCCO())->addAction(new Talk('Thank you for trying Vonage')))
        ->setLengthTimer(7200)
        ->setRingingTimer(60);
    $callData = $this->voiceClient->createOutboundCall($outboundCall);

    $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $callData->getUuid());
    $this->assertEquals('started', $callData->getStatus());
    $this->assertEquals('outbound', $callData->getDirection());
    $this->assertEquals('2541d01c-253e-48be-a8e0-da4bbe4c3722', $callData->getConversationUuid());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can handle error while creating outbound call', function () {
    $this->expectException(RequestException::class);
    $this->expectExceptionMessage('Bad Request');

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);

        return true;
    }))->willReturn(getResponse('error_vapi', 400));

    $outboundCall = new OutboundCall(new Phone('15555555555'), new Phone('16666666666'));
    $this->voiceClient->createOutboundCall($outboundCall);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('create outbound call error unknown format', function () {
    $this->expectException(RequestException::class);
    $this->expectExceptionMessage("Unexpected error");

    $this->vonageClient->send(Argument::that(function () {
        return true;
    }))->willReturn(getResponse('error_unknown_format', 400));

    $outboundCall = new OutboundCall(new Phone('15555555555'), new Phone('16666666666'));
    $this->voiceClient->createOutboundCall($outboundCall);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can retrieve call information', function () {
    $id = '63f61863-4a51-4f6b-86e1-46edebcf9356';
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
        return true;
    }))->willReturn(getResponse('call', 200));

    $call = $this->voiceClient->get($id);

    $this->assertEquals($id, $call->getUuid());
    $this->assertEquals('447700900000', $call->getTo()->getId());
    $this->assertEquals('447700900001', $call->getFrom()->getId());
    $this->assertEquals('started', $call->getStatus());
    $this->assertEquals('outbound', $call->getDirection());
    $this->assertEquals('0.39', $call->getRate());
    $this->assertEquals('23.40', $call->getPrice());
    $this->assertEquals('60', $call->getDuration());
    $this->assertEquals('2020-01-01 12:00:00', $call->getStartTime()->format('Y-m-d H:i:s'));
    $this->assertEquals('2020-01-01 12:00:00', $call->getEndTime()->format('Y-m-d H:i:s'));
    $this->assertEquals('65512', $call->getNetwork());
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can modify a call leg', function () {
    $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
    $payload = ['action' => 'earmuff'];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('empty', 204));

    $this->voiceClient->modifyCall($id, CallAction::EARMUFF);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can earmuff call', function () {
    $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
    $payload = ['action' => 'earmuff'];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('empty', 204));

    $this->voiceClient->earmuffCall($id);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can unearmuff call', function () {
    $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
    $payload = ['action' => 'unearmuff'];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('empty', 204));

    $this->voiceClient->unearmuffCall($id);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can mute call', function () {
    $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
    $payload = ['action' => 'mute'];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('empty', 204));

    $this->voiceClient->muteCall($id);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can unmute call', function () {
    $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
    $payload = ['action' => 'unmute'];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('empty', 204));

    $this->voiceClient->unmuteCall($id);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can hangup call', function () {
    $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
    $payload = ['action' => 'hangup'];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('empty', 204));

    $this->voiceClient->hangupCall($id);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can transfer a call leg with n c c o', function () {
    $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
    $payload = [
        'action' => 'transfer',
        'destination' => [
            'type' => 'ncco',
            'ncco' => [
                [
                    'action' => 'talk',
                    'text' => 'Thank you for trying Vonage',
                ]
            ]
        ],
    ];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('empty', 204));

    $ncco = (new NCCO())
        ->addAction(new Talk('Thank you for trying Vonage'));

    $this->voiceClient->transferCallWithNCCO($id, $ncco);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can transfer a call leg with u r l', function () {
    $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
    $payload = [
        'action' => 'transfer',
        'destination' => [
            'type' => 'ncco',
            'url' => ['https://test.domain/transfer.json'],
        ],
    ];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('empty', 204));

    $this->voiceClient->transferCallWithUrl($id, 'https://test.domain/transfer.json');
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('testcan stream audio into call', function () {
    $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
    $url = 'http://domain.test/music.mp3';
    $payload = [
        'stream_url' => [$url],
        'loop' => '1',
        'level' => '0',
    ];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/stream', 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('stream'));

    $response = $this->voiceClient->streamAudio($id, $url);

    $this->assertEquals($id, $response['uuid']);
    $this->assertEquals('Stream started', $response['message']);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can stop streaming audio into call', function () {
    $id = '63f61863-4a51-4f6b-86e1-46edebcf9356';

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/stream', 'DELETE', $request);
        return true;
    }))->willReturn(getResponse('stream-stopped'));

    $response = $this->voiceClient->stopStreamAudio($id);

    $this->assertEquals($id, $response['uuid']);
    $this->assertEquals('Stream stopped', $response['message']);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can play t t s into call', function () {
    $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
    $payload = [
        'text' => 'This is sample text',
    ];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/talk', 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('talk'));

    $action = new Talk('This is sample text');
    $response = $this->voiceClient->playTTS($id, $action);

    $this->assertEquals($id, $response['uuid']);
    $this->assertEquals('Talk started', $response['message']);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can stop t t s in call', function () {
    $id = '63f61863-4a51-4f6b-86e1-46edebcf9356';

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/talk', 'DELETE', $request);

        return true;
    }))->willReturn(getResponse('talk-stopped'));

    $response = $this->voiceClient->stopTTS($id);

    $this->assertEquals($id, $response['uuid']);
    $this->assertEquals('Talk stopped', $response['message']);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 */
test('can play d t m f into call', function () {
    $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
    $payload = ['digits' => '1492'];

    $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/dtmf', 'PUT', $request);
        $this->assertRequestBodyIsJson(json_encode($payload), $request);

        return true;
    }))->willReturn(getResponse('dtmf'));

    $response = $this->voiceClient->playDTMF($id, $payload['digits']);

    $this->assertEquals($id, $response['uuid']);
    $this->assertEquals('DTMF sent', $response['message']);
});

/**
 * @throws ClientExceptionInterface
 * @throws Client\Exception\Exception
 * @throws Client\Exception\Server
 * @throws RequestException
 */
test('can search calls', function () {
    $response = getResponse('search');
    $data = json_decode($response->getBody()->getContents(), true);
    $response->getBody()->rewind();
    $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
        $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'GET', $request);
        $this->assertRequestQueryContains('page_size', '10', $request);
        $this->assertRequestQueryContains('record_index', '0', $request);
        $this->assertRequestQueryContains('order', 'asc', $request);
        $this->assertRequestQueryContains('status', VoiceFilter::STATUS_STARTED, $request);

        return true;
    }))->willReturn($response);

    $filter = new VoiceFilter();
    $filter->setStatus(VoiceFilter::STATUS_STARTED);
    $response = $this->voiceClient->search($filter);

    $this->assertCount(1, $response);

    $call = $response->current();

    $this->assertEquals($data['_embedded']['calls'][0]['uuid'], $call->getUuid());
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success', int $status = 200): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
}
