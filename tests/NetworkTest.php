<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

use VonageTest\VonageTestCase;
use Vonage\Network;

uses(VonageTestCase::class);

test('network array access', function () {
    $network = new Network('12345', 'Demo Network');

    $this->assertEquals('12345', @$network['network_code']);
    $this->assertEquals('Demo Network', @$network['network_name']);
});

test('network getters', function () {
    $network = new Network('12345', 'Demo Network');

    $this->assertEquals('12345', $network->getCode());
    $this->assertEquals('Demo Network', $network->getName());
});

test('network from array', function () {
    $network = new Network('12345', 'Demo Network');
    $network->fromArray([
        'type' => 'mobile',
        'networkCode' => '12345',
        'networkName' => 'Demo Network',
        'sms_price' => '0.0331',
        'voice_price' => '0.0123',
        'currency' => 'EUR',
        'mcc' => '310',
        'mnc' => '740',
    ]);

    $this->assertEquals('12345', $network->getCode());
    $this->assertEquals('Demo Network', $network->getName());
    $this->assertEquals('0.0331', $network->getOutboundSmsPrice());
    $this->assertEquals('0.0123', $network->getOutboundVoicePrice());
    $this->assertEquals('EUR', $network->getCurrency());
});

test('sms price fallback', function () {
    $network = new Network('12345', 'Demo Network');
    $network->fromArray(['price' => '0.0331']);

    $this->assertEquals('0.0331', $network->getOutboundSmsPrice());
});

test('voice price fallback', function () {
    $network = new Network('12345', 'Demo Network');
    $network->fromArray(['price' => '0.0331']);

    $this->assertEquals('0.0331', $network->getOutboundSmsPrice());
});
