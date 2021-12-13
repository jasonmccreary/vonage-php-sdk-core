<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Vonage\Account\SmsPrice;

/**
 *
 * @param $smsPrice
 */
test('from array', function ($smsPrice) {
    expect($smsPrice->getCountryCode())->toEqual("US");
    expect($smsPrice->getCountryName())->toEqual("United States");
    expect($smsPrice->getDialingPrefix())->toEqual("1");
    expect($smsPrice->getDefaultPrice())->toEqual("0.00512");
})->with('smsPriceProvider');

/**
 *
 * @param $smsPrice
 */
test('getters', function ($smsPrice) {
    expect($smsPrice->getCountryCode())->toEqual("US");
    expect($smsPrice->getCountryName())->toEqual("United States");
    expect($smsPrice->getCountryDisplayName())->toEqual("United States");
    expect($smsPrice->getDialingPrefix())->toEqual("1");
    expect($smsPrice->getDefaultPrice())->toEqual("0.00512");
})->with('smsPriceProvider');

/**
 *
 * @param $smsPrice
 */
test('array access', function ($smsPrice) {
    expect(@$smsPrice['country_code'])->toEqual("US");
    expect(@$smsPrice['country_name'])->toEqual("United States");
    expect(@$smsPrice['country_display_name'])->toEqual("United States");
    expect(@$smsPrice['dialing_prefix'])->toEqual("1");
    expect(@$smsPrice['default_price'])->toEqual("0.00512");
})->with('smsPriceProvider');

/**
 *
 * @param $smsPrice
 */
test('uses custom price for known network', function ($smsPrice) {
    expect($smsPrice->getPriceForNetwork('21039'))->toEqual("0.123");
})->with('smsPriceProvider');

/**
 *
 * @param $smsPrice
 */
test('uses default price for unknown network', function ($smsPrice) {
    expect($smsPrice->getPriceForNetwork('007'))->toEqual("0.00512");
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
