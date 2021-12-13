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
use Vonage\Application\Application;
use Vonage\Application\MessagesConfig;
use Vonage\Application\RtcConfig;
use Vonage\Application\VoiceConfig;

uses(VonageTestCase::class);
use Vonage\Client\Exception\Exception as ClientException;


beforeEach(function () {
    app() = (new Application())->setName('test');
});

test('construct with id', function () {
    $app = new Application('1a20a124-1775-412b-b623-e6985f4aace0');

    expect($app->getId())->toEqual('1a20a124-1775-412b-b623-e6985f4aace0');
});

/**
 * @throws ClientException
 */
test('name is set', function () {
    expect(@app()->getRequestData()['name'])->toEqual('test');
});

/**
 * @throws Exception
 */
test('voice webhook params', function () {
    @app()->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'http://example.com/event');
    @app()->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'http://example.com/answer');

    $params = @app()->getRequestData();
    $capabilities = $params['capabilities'];

    $this->assertArrayHasKey('event_url', $capabilities['voice']['webhooks']);
    $this->assertArrayHasKey('answer_url', $capabilities['voice']['webhooks']);
    expect($capabilities['voice']['webhooks']['event_url']['address'])->toEqual('http://example.com/event');
    expect($capabilities['voice']['webhooks']['answer_url']['address'])->toEqual('http://example.com/answer');
});

test('response sets properties', function () {
    @app()->setResponse(getResponse());

    expect(app()->getName())->toEqual('My Application');
    $this->assertEquals(
        "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi" .
        "+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851" .
        "Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n",
        app()->getPublicKey()
    );
    expect(app()->getPrivateKey())->toEqual('private_key');
});

/**
 * @throws Exception
 */
test('response sets voice configs', function () {
    @app()->setResponse(getResponse());

    $webhook = app()->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
    $method = app()->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER)->getMethod();
    expect($webhook)->toEqual('https://example.com/webhooks/answer');
    expect($method)->toEqual('GET');

    $webhook = app()->getVoiceConfig()->getWebhook(VoiceConfig::EVENT);
    $method = app()->getVoiceConfig()->getWebhook(VoiceConfig::EVENT)->getMethod();
    expect($webhook)->toEqual('https://example.com/webhooks/event');
    expect($method)->toEqual('POST');
});

/**
 * @throws Exception
 */
test('response sets messages configs', function () {
    @app()->setResponse(getResponse());

    $webhook = app()->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND);
    $method = app()->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND)->getMethod();
    expect($webhook)->toEqual('https://example.com/webhooks/inbound');
    expect($method)->toEqual('POST');

    $webhook = app()->getMessagesConfig()->getWebhook(MessagesConfig::STATUS);
    $method = app()->getMessagesConfig()->getWebhook(MessagesConfig::STATUS)->getMethod();
    expect($webhook)->toEqual('https://example.com/webhooks/status');
    expect($method)->toEqual('POST');
});

/**
 * @throws Exception
 */
test('response sets rtc configs', function () {
    @app()->setResponse(getResponse());

    $webhook = app()->getRtcConfig()->getWebhook(RtcConfig::EVENT);
    $method = app()->getRtcConfig()->getWebhook(RtcConfig::EVENT)->getMethod();
    expect($webhook)->toEqual('https://example.com/webhooks/event');
    expect($method)->toEqual('POST');
});

test('response sets vbc configs', function () {
    @app()->setResponse(getResponse());
    expect(app()->getVbcConfig()->isEnabled())->toEqual(true);
});

/**
 * @throws Exception
 */
test('can get dirty values', function () {
    @app()->setResponse(getResponse());
    expect(app()->getName())->toEqual('My Application');

    app()->setName('new');
    expect(app()->getName())->toEqual('new');

    $webhook = app()->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
    expect($webhook)->toEqual('https://example.com/webhooks/answer');

    @app()->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'http://example.com');
    $webhook = app()->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
    $this->assertEquals('http://example.com', (string)$webhook);
});

/**
 * @throws Exception
 */
test('config can be copied', function () {
    @app()->setResponse(getResponse());

    $otherapp = new Application();
    $otherapp->setName('new app');

    $otherapp->setVoiceConfig(app()->getVoiceConfig());

    $webhook = $otherapp->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
    expect($webhook)->toEqual('https://example.com/webhooks/answer');
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success'): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
}
