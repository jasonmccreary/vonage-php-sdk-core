<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Application\Application;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Numbers\Number;

uses(VonageTestCase::class);

use function file_get_contents;
use function json_decode;

beforeEach(function () {
    $this->number = new Number();
});

test('construct with id', function () {
    $number = new Number('14843331212');

    $this->assertEquals('14843331212', $number->getId());
    $this->assertEquals('14843331212', $number->getMsisdn());
    $this->assertEquals('14843331212', $number->getNumber());
});

test('construct with id and country', function () {
    $number = new Number('14843331212', 'US');

    $this->assertEquals('US', $number->getCountry());
});

test('hydrate', function () {
    $data = json_decode(file_get_contents(__DIR__ . '/responses/single.json'), true);
    $this->number->fromArray($data['numbers'][0]);

    $this->assertEquals('US', $this->number->getCountry());
    $this->assertEquals('1415550100', $this->number->getNumber());
    $this->assertEquals(Number::TYPE_MOBILE, $this->number->getType());
    $this->assertEquals('http://example.com/message', $this->number->getWebhook(Number::WEBHOOK_MESSAGE));
    $this->assertEquals('http://example.com/status', $this->number->getWebhook(Number::WEBHOOK_VOICE_STATUS));
    $this->assertEquals('http://example.com/voice', $this->number->getVoiceDestination());
    $this->assertEquals(Number::ENDPOINT_VXML, $this->number->getVoiceType());
    $this->assertTrue($this->number->hasFeature(Number::FEATURE_VOICE));
    $this->assertTrue($this->number->hasFeature(Number::FEATURE_SMS));
    $this->assertContains(Number::FEATURE_VOICE, $this->number->getFeatures());
    $this->assertContains(Number::FEATURE_SMS, $this->number->getFeatures());
    $this->assertCount(2, $this->number->getFeatures());
});

test('available numbers', function () {
    $data = json_decode(file_get_contents(__DIR__ . '/responses/available-numbers.json'), true);
    $this->number->fromArray($data['numbers'][0]);

    $this->assertEquals('US', $this->number->getCountry());
    $this->assertEquals('14155550100', $this->number->getNumber());
    $this->assertEquals(Number::TYPE_MOBILE, $this->number->getType());
    $this->assertEquals('0.67', $this->number->getCost());
    $this->assertTrue($this->number->hasFeature(Number::FEATURE_VOICE));
    $this->assertTrue($this->number->hasFeature(Number::FEATURE_SMS));
    $this->assertContains(Number::FEATURE_VOICE, $this->number->getFeatures());
    $this->assertContains(Number::FEATURE_SMS, $this->number->getFeatures());
    $this->assertCount(2, $this->number->getFeatures());
});

/**
 * @throws ClientException
 */
test('voice application', function () {
    $id = 'abcd-1234-edfg';

    $this->number->setVoiceDestination($id);
    $app = $this->number->getVoiceDestination();

    $this->assertInstanceOf(Application::class, $app);
    $this->assertEquals($id, $app->getId());
    $this->assertArrayHas('app_id', $id, $this->number->getRequestData());

    $app = new Application($id);
    $this->number->setVoiceDestination($app);

    $this->assertSame($app, $this->number->getVoiceDestination());
    $this->assertArrayHas('app_id', $id, $this->number->getRequestData());
});

/**
 * @throws ClientException
 */
test('force voice type', function () {
    $this->number->setVoiceDestination('not-valid', NUMBER::ENDPOINT_SIP);

    $this->assertSame(Number::ENDPOINT_SIP, $this->number->getVoiceType());
    $this->assertArrayHas('voiceCallbackType', Number::ENDPOINT_SIP, $this->number->getRequestData());
});

/**
 *
 * @param $type
 * @param $value
 *
 * @throws ClientException
 */
test('voice destination', function ($type, $value) {
    $this->assertSame($this->number, $this->number->setVoiceDestination($value));
    $this->assertEquals($value, $this->number->getVoiceDestination());
    $this->assertEquals($type, $this->number->getVoiceType());
    $this->assertArrayHas('voiceCallbackType', $type, $this->number->getRequestData());
    $this->assertArrayHas('voiceCallbackValue', $value, $this->number->getRequestData());
})->with('voiceDestinations');

test('system type', function () {
    $numberData = [
        'msisdn' => '447700900000',
        'type' => Number::TYPE_FIXED,
    ];
    $number = new Number();
    $number->fromArray($numberData);

    $this->assertEquals($numberData['type'], $number->getType());
});

/**
 * @throws ClientException
 */
test('status webhook', function () {
    $this->assertSame($this->number, $this->number->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'http://example.com'));
    $this->assertEquals('http://example.com', $this->number->getWebhook(Number::WEBHOOK_VOICE_STATUS));
    $this->assertArrayHas('voiceStatusCallbackUrl', 'http://example.com', $this->number->getRequestData());
});

/**
 * @throws ClientException
 */
test('message webhook', function () {
    $this->assertSame($this->number, $this->number->setWebhook(Number::WEBHOOK_MESSAGE, 'http://example.com'));
    $this->assertEquals('http://example.com', $this->number->getWebhook(Number::WEBHOOK_MESSAGE));
    $this->assertArrayHas('moHttpUrl', 'http://example.com', $this->number->getRequestData());
});

// Datasets
/**
 * @return array[]
 */
dataset('voiceDestinations', [
    [Number::ENDPOINT_SIP, 'user@example.com'],
    [Number::ENDPOINT_TEL, '14843331212'],
    [Number::ENDPOINT_VXML, 'http://example.com']
]);

// Helpers
function assertArrayHas($key, $value, $array): void
{
    self::assertArrayHasKey($key, $array);
    self::assertEquals($value, $array[$key]);
}
