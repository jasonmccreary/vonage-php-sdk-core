<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Exception;
use Laminas\Diactoros\Response;
use VonageTest\VonageTestCase;
use Vonage\Application\Application;
use Vonage\Application\MessagesConfig;
use Vonage\Application\RtcConfig;
use Vonage\Application\VoiceConfig;

uses(VonageTestCase::class);
use Vonage\Client\Exception\Exception as ClientException;

use function fopen;

beforeEach(function () {
    app() = (new Application())->setName('test');
});

test('construct with id', function () {
    $app = new Application('1a20a124-1775-412b-b623-e6985f4aace0');

    $this->assertEquals('1a20a124-1775-412b-b623-e6985f4aace0', $app->getId());
});

/**
 * @throws ClientException
 */
test('name is set', function () {
    $this->assertEquals('test', @app()->getRequestData()['name']);
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
    $this->assertEquals('http://example.com/event', $capabilities['voice']['webhooks']['event_url']['address']);
    $this->assertEquals('http://example.com/answer', $capabilities['voice']['webhooks']['answer_url']['address']);
});

test('response sets properties', function () {
    @app()->setResponse(getResponse());

    $this->assertEquals('My Application', app()->getName());
    $this->assertEquals(
        "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi" .
        "+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851" .
        "Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n",
        app()->getPublicKey()
    );
    $this->assertEquals('private_key', app()->getPrivateKey());
});

/**
 * @throws Exception
 */
test('response sets voice configs', function () {
    @app()->setResponse(getResponse());

    $webhook = app()->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
    $method = app()->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER)->getMethod();
    $this->assertEquals('https://example.com/webhooks/answer', $webhook);
    $this->assertEquals('GET', $method);

    $webhook = app()->getVoiceConfig()->getWebhook(VoiceConfig::EVENT);
    $method = app()->getVoiceConfig()->getWebhook(VoiceConfig::EVENT)->getMethod();
    $this->assertEquals('https://example.com/webhooks/event', $webhook);
    $this->assertEquals('POST', $method);
});

/**
 * @throws Exception
 */
test('response sets messages configs', function () {
    @app()->setResponse(getResponse());

    $webhook = app()->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND);
    $method = app()->getMessagesConfig()->getWebhook(MessagesConfig::INBOUND)->getMethod();
    $this->assertEquals('https://example.com/webhooks/inbound', $webhook);
    $this->assertEquals('POST', $method);

    $webhook = app()->getMessagesConfig()->getWebhook(MessagesConfig::STATUS);
    $method = app()->getMessagesConfig()->getWebhook(MessagesConfig::STATUS)->getMethod();
    $this->assertEquals('https://example.com/webhooks/status', $webhook);
    $this->assertEquals('POST', $method);
});

/**
 * @throws Exception
 */
test('response sets rtc configs', function () {
    @app()->setResponse(getResponse());

    $webhook = app()->getRtcConfig()->getWebhook(RtcConfig::EVENT);
    $method = app()->getRtcConfig()->getWebhook(RtcConfig::EVENT)->getMethod();
    $this->assertEquals('https://example.com/webhooks/event', $webhook);
    $this->assertEquals('POST', $method);
});

test('response sets vbc configs', function () {
    @app()->setResponse(getResponse());
    $this->assertEquals(true, app()->getVbcConfig()->isEnabled());
});

/**
 * @throws Exception
 */
test('can get dirty values', function () {
    @app()->setResponse(getResponse());
    $this->assertEquals('My Application', app()->getName());

    app()->setName('new');
    $this->assertEquals('new', app()->getName());

    $webhook = app()->getVoiceConfig()->getWebhook(VoiceConfig::ANSWER);
    $this->assertEquals('https://example.com/webhooks/answer', $webhook);

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
    $this->assertEquals('https://example.com/webhooks/answer', $webhook);
});

// Helpers
/**
     * Get the API response we'd expect for a call to the API.
     */
function getResponse(string $type = 'success'): Response
{
    return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
}
