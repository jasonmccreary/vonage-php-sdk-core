<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Insights\Standard;

uses(VonageTestCase::class);

/**
 *
 * @param $standard
 * @param $inputData
 */
test('array access', function ($standard, $inputData) {
    expect(@$standard['refund_price'])->toEqual($inputData['refund_price']);
    expect(@$standard['request_price'])->toEqual($inputData['request_price']);
    expect(@$standard['remaining_balance'])->toEqual($inputData['remaining_balance']);
    expect(@$standard['current_carrier'])->toEqual($inputData['current_carrier']);
    expect(@$standard['original_carrier'])->toEqual($inputData['original_carrier']);
    expect(@$standard['ported'])->toEqual($inputData['ported']);
    expect(@$standard['roaming'])->toEqual($inputData['roaming']);
})->with('standardTestProvider');

/**
 *
 * @param $standard
 * @param $inputData
 */
test('object access', function ($standard, $inputData) {
    expect(@$standard->getRefundPrice())->toEqual($inputData['refund_price']);
    expect(@$standard->getRequestPrice())->toEqual($inputData['request_price']);
    expect(@$standard->getRemainingBalance())->toEqual($inputData['remaining_balance']);
    expect($standard->getCurrentCarrier())->toEqual($inputData['current_carrier']);
    expect($standard->getOriginalCarrier())->toEqual($inputData['original_carrier']);
    expect($standard->getPorted())->toEqual($inputData['ported']);
    expect($standard->getRoaming())->toEqual($inputData['roaming']);
})->with('standardTestProvider');

// Datasets
dataset('standardTestProvider', function () {
    $r = [];

    $input1 = [
        'current_carrier' =>
            [
                'network_code' => '23420',
                'name' => 'Hutchison 3G Ltd',
                'country' => 'GB',
                'network_type' => 'mobile',
            ],
        'original_carrier' =>
            [
                'network_code' => '23430',
                'name' => 'EE Tmobile',
                'country' => 'GB',
                'network_type' => 'mobile',
            ],
        'ported' => 'assumed_ported',
        'request_price' => '0.00500000',
        'refund_price' => '0.00500000',
        'remaining_balance' => '26.294675',
        'roaming' =>
            [
                'status' => 'unknown',
            ],
    ];

    $standard1 = new Standard('01234567890');
    $standard1->fromArray($input1);
    $r['standard-1'] = [$standard1, $input1];

    return $r;
});
