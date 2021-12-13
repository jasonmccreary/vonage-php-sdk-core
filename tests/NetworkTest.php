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

    expect(@$network['network_code'])->toEqual('12345');
    expect(@$network['network_name'])->toEqual('Demo Network');
});

test('network getters', function () {
    $network = new Network('12345', 'Demo Network');

    expect($network->getCode())->toEqual('12345');
    expect($network->getName())->toEqual('Demo Network');
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

    expect($network->getCode())->toEqual('12345');
    expect($network->getName())->toEqual('Demo Network');
    expect($network->getOutboundSmsPrice())->toEqual('0.0331');
    expect($network->getOutboundVoicePrice())->toEqual('0.0123');
    expect($network->getCurrency())->toEqual('EUR');
});

test('sms price fallback', function () {
    $network = new Network('12345', 'Demo Network');
    $network->fromArray(['price' => '0.0331']);

    expect($network->getOutboundSmsPrice())->toEqual('0.0331');
});

test('voice price fallback', function () {
    $network = new Network('12345', 'Demo Network');
    $network->fromArray(['price' => '0.0331']);

    expect($network->getOutboundSmsPrice())->toEqual('0.0331');
});
