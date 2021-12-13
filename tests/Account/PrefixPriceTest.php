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
    expect($prefixPrice->getCountryCode())->toEqual("ZW");
    expect($prefixPrice->getCountryName())->toEqual("Zimbabwe");
    expect($prefixPrice->getDialingPrefix())->toEqual("263");
})->with('prefixPriceProvider');

/**
 *
 * @param $prefixPrice
 */
test('getters', function ($prefixPrice) {
    expect($prefixPrice->getCountryCode())->toEqual("ZW");
    expect($prefixPrice->getCountryName())->toEqual("Zimbabwe");
    expect($prefixPrice->getCountryDisplayName())->toEqual("Zimbabwe");
    expect($prefixPrice->getDialingPrefix())->toEqual("263");
})->with('prefixPriceProvider');

/**
 *
 * @param $prefixPrice
 */
test('array access', function ($prefixPrice) {
    expect(@$prefixPrice['country_code'])->toEqual("ZW");
    expect(@$prefixPrice['country_name'])->toEqual("Zimbabwe");
    expect(@$prefixPrice['country_display_name'])->toEqual("Zimbabwe");
    expect(@$prefixPrice['dialing_prefix'])->toEqual("263");
})->with('prefixPriceProvider');

/**
 *
 * @param $prefixPrice
 */
test('uses custom price for known network', function ($prefixPrice) {
    expect($prefixPrice->getPriceForNetwork('21039'))->toEqual("0.123");
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
