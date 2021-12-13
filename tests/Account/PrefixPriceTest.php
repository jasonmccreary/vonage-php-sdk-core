<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Account\PrefixPrice;

uses(VonageTestCase::class);

/**
 *
 * @param $prefixPrice
 */
test('from array', function ($prefixPrice) {
    $this->assertEquals("ZW", $prefixPrice->getCountryCode());
    $this->assertEquals("Zimbabwe", $prefixPrice->getCountryName());
    $this->assertEquals("263", $prefixPrice->getDialingPrefix());
})->with('prefixPriceProvider');

/**
 *
 * @param $prefixPrice
 */
test('getters', function ($prefixPrice) {
    $this->assertEquals("ZW", $prefixPrice->getCountryCode());
    $this->assertEquals("Zimbabwe", $prefixPrice->getCountryName());
    $this->assertEquals("Zimbabwe", $prefixPrice->getCountryDisplayName());
    $this->assertEquals("263", $prefixPrice->getDialingPrefix());
})->with('prefixPriceProvider');

/**
 *
 * @param $prefixPrice
 */
test('array access', function ($prefixPrice) {
    $this->assertEquals("ZW", @$prefixPrice['country_code']);
    $this->assertEquals("Zimbabwe", @$prefixPrice['country_name']);
    $this->assertEquals("Zimbabwe", @$prefixPrice['country_display_name']);
    $this->assertEquals("263", @$prefixPrice['dialing_prefix']);
})->with('prefixPriceProvider');

/**
 *
 * @param $prefixPrice
 */
test('uses custom price for known network', function ($prefixPrice) {
    $this->assertEquals("0.123", $prefixPrice->getPriceForNetwork('21039'));
})->with('prefixPriceProvider');

/**
 * @throws \Vonage\Client\Exception\Exception
 */
test('cannot get currency', function () {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Currency is unavailable from this endpoint');

    $prefixPrice = new PrefixPrice();
    $prefixPrice->getCurrency();
});

// Datasets
dataset('prefixPriceProvider', function () {
    $r = [];

    $prefixPrice = new PrefixPrice();
    @$prefixPrice->fromArray([
        'country' => 'ZW',
        'name' => 'Zimbabwe',
        'prefix' => 263,
        'networks' => [
            [
                'code' => '21039',
                'network' => 'Demo Network',
                'mtPrice' => '0.123'
            ]
        ]
    ]);
    $r['jsonUnserialize'] = [$prefixPrice];

    return $r;
});
