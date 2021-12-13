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



beforeEach(function () {
    $this->number = new Number();
});

test('construct with id', function () {
    $number = new Number('14843331212');

    expect($number->getId())->toEqual('14843331212');
    expect($number->getMsisdn())->toEqual('14843331212');
    expect($number->getNumber())->toEqual('14843331212');
});

test('construct with id and country', function () {
    $number = new Number('14843331212', 'US');

    expect($number->getCountry())->toEqual('US');
});

test('hydrate', function () {
    $data = json_decode(file_get_contents(__DIR__ . '/responses/single.json'), true);
    $this->number->fromArray($data['numbers'][0]);

    expect($this->number->getCountry())->toEqual('US');
    expect($this->number->getNumber())->toEqual('1415550100');
    expect($this->number->getType())->toEqual(Number::TYPE_MOBILE);
    expect($this->number->getWebhook(Number::WEBHOOK_MESSAGE))->toEqual('http://example.com/message');
    expect($this->number->getWebhook(Number::WEBHOOK_VOICE_STATUS))->toEqual('http://example.com/status');
    expect($this->number->getVoiceDestination())->toEqual('http://example.com/voice');
    expect($this->number->getVoiceType())->toEqual(Number::ENDPOINT_VXML);
    expect($this->number->hasFeature(Number::FEATURE_VOICE))->toBeTrue();
    expect($this->number->hasFeature(Number::FEATURE_SMS))->toBeTrue();
    expect($this->number->getFeatures())->toContain(Number::FEATURE_VOICE);
    expect($this->number->getFeatures())->toContain(Number::FEATURE_SMS);
    expect($this->number->getFeatures())->toHaveCount(2);
});

test('available numbers', function () {
    $data = json_decode(file_get_contents(__DIR__ . '/responses/available-numbers.json'), true);
    $this->number->fromArray($data['numbers'][0]);

    expect($this->number->getCountry())->toEqual('US');
    expect($this->number->getNumber())->toEqual('14155550100');
    expect($this->number->getType())->toEqual(Number::TYPE_MOBILE);
    expect($this->number->getCost())->toEqual('0.67');
    expect($this->number->hasFeature(Number::FEATURE_VOICE))->toBeTrue();
    expect($this->number->hasFeature(Number::FEATURE_SMS))->toBeTrue();
    expect($this->number->getFeatures())->toContain(Number::FEATURE_VOICE);
    expect($this->number->getFeatures())->toContain(Number::FEATURE_SMS);
    expect($this->number->getFeatures())->toHaveCount(2);
});

/**
 * @throws ClientException
 */
test('voice application', function () {
    $id = 'abcd-1234-edfg';

    $this->number->setVoiceDestination($id);
    $app = $this->number->getVoiceDestination();

    expect($app)->toBeInstanceOf(Application::class);
    expect($app->getId())->toEqual($id);
    $this->assertArrayHas('app_id', $id, $this->number->getRequestData());

    $app = new Application($id);
    $this->number->setVoiceDestination($app);

    expect($this->number->getVoiceDestination())->toBe($app);
    $this->assertArrayHas('app_id', $id, $this->number->getRequestData());
});

/**
 * @throws ClientException
 */
test('force voice type', function () {
    $this->number->setVoiceDestination('not-valid', NUMBER::ENDPOINT_SIP);

    expect($this->number->getVoiceType())->toBe(Number::ENDPOINT_SIP);
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
    expect($this->number->setVoiceDestination($value))->toBe($this->number);
    expect($this->number->getVoiceDestination())->toEqual($value);
    expect($this->number->getVoiceType())->toEqual($type);
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

    expect($number->getType())->toEqual($numberData['type']);
});

/**
 * @throws ClientException
 */
test('status webhook', function () {
    expect($this->number->setWebhook(Number::WEBHOOK_VOICE_STATUS, 'http://example.com'))->toBe($this->number);
    expect($this->number->getWebhook(Number::WEBHOOK_VOICE_STATUS))->toEqual('http://example.com');
    $this->assertArrayHas('voiceStatusCallbackUrl', 'http://example.com', $this->number->getRequestData());
});

/**
 * @throws ClientException
 */
test('message webhook', function () {
    expect($this->number->setWebhook(Number::WEBHOOK_MESSAGE, 'http://example.com'))->toBe($this->number);
    expect($this->number->getWebhook(Number::WEBHOOK_MESSAGE))->toEqual('http://example.com');
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
    expect($array[$key])->toEqual($value);
}
