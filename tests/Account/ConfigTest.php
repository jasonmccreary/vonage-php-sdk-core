<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Account\Config;

uses(VonageTestCase::class);

beforeEach(function () {
    $this->config = new Config(
        "https://example.com/webhooks/inbound-sms",
        "https://example.com/webhooks/delivery-receipt",
        30, // different values so we can check if we reversed one anywhere
        31,
        32
    );
});

test('object access', function () {
    expect($this->config->getSmsCallbackUrl())->toEqual("https://example.com/webhooks/inbound-sms");
    expect($this->config->getDrCallbackUrl())->toEqual("https://example.com/webhooks/delivery-receipt");
    expect($this->config->getMaxOutboundRequest())->toEqual(30);
    expect($this->config->getMaxInboundRequest())->toEqual(31);
    expect($this->config->getMaxCallsPerSecond())->toEqual(32);
});

test('array access', function () {
    expect(@$this->config['sms_callback_url'])->toEqual("https://example.com/webhooks/inbound-sms");
    expect(@$this->config['dr_callback_url'])->toEqual("https://example.com/webhooks/delivery-receipt");
    expect(@$this->config['max_outbound_request'])->toEqual(30);
    expect(@$this->config['max_inbound_request'])->toEqual(31);
    expect(@$this->config['max_calls_per_second'])->toEqual(32);
});
