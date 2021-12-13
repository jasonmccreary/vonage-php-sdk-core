<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Account\SmsPrice;

uses(VonageTestCase::class);

/**
 *
 * @param $smsPrice
 */
test('from array', function ($smsPrice) {
    $this->assertEquals("US", $smsPrice->getCountryCode());
    $this->assertEquals("United States", $smsPrice->getCountryName());
    $this->assertEquals("1", $smsPrice->getDialingPrefix());
    $this->assertEquals("0.00512", $smsPrice->getDefaultPrice());
})->with('smsPriceProvider');

/**
 *
 * @param $smsPrice
 */
test('getters', function ($smsPrice) {
    $this->assertEquals("US", $smsPrice->getCountryCode());
    $this->assertEquals("United States", $smsPrice->getCountryName());
    $this->assertEquals("United States", $smsPrice->getCountryDisplayName());
    $this->assertEquals("1", $smsPrice->getDialingPrefix());
    $this->assertEquals("0.00512", $smsPrice->getDefaultPrice());
})->with('smsPriceProvider');

/**
 *
 * @param $smsPrice
 */
test('array access', function ($smsPrice) {
    $this->assertEquals("US", @$smsPrice['country_code']);
    $this->assertEquals("United States", @$smsPrice['country_name']);
    $this->assertEquals("United States", @$smsPrice['country_display_name']);
    $this->assertEquals("1", @$smsPrice['dialing_prefix']);
    $this->assertEquals("0.00512", @$smsPrice['default_price']);
})->with('smsPriceProvider');

/**
 *
 * @param $smsPrice
 */
test('uses custom price for known network', function ($smsPrice) {
    $this->assertEquals("0.123", $smsPrice->getPriceForNetwork('21039'));
})->with('smsPriceProvider');

/**
 *
 * @param $smsPrice
 */
test('uses default price for unknown network', function ($smsPrice) {
    $this->assertEquals("0.00512", $smsPrice->getPriceForNetwork('007'));
})->with('smsPriceProvider');

// Datasets
dataset('smsPriceProvider', function () {
    $r = [];

    $smsPrice = new SmsPrice();
    @$smsPrice->fromArray([
        'dialing_prefix' => 1,
        'default_price' => '0.00512',
        'currency' => 'EUR',
        'country_code' => 'US',
        'country_name' => 'United States',
        'country_display_name' => 'United States',
        'prefix' => 1,
        'networks' => [
            [
                'currency' => 'EUR',
                'networkCode' => '21039',
                'networkName' => 'Demo Network',
                'price' => '0.123'
            ]
        ]
    ]);
    $r['jsonUnserialize'] = [$smsPrice];

    return $r;
});
