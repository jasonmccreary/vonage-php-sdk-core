<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Insights\Advanced;


/**
 *
 * @param $advanced
 * @param $inputData
 */
test('array access', function ($advanced, $inputData) {
    expect(@$advanced['valid_number'])->toEqual($inputData['valid_number']);
    expect(@$advanced['reachable'])->toEqual($inputData['reachable']);
})->with('advancedTestProvider');

/**
 *
 * @param $advanced
 * @param $inputData
 */
test('object access', function ($advanced, $inputData) {
    expect($advanced->getValidNumber())->toEqual($inputData['valid_number']);
    expect($advanced->getReachable())->toEqual($inputData['reachable']);
})->with('advancedTestProvider');

// Datasets
dataset('advancedTestProvider', function () {
    $r = [];

    $input1 = [
        'valid_number' => 'valid',
        'reachable' => 'unknown'
    ];

    $advanced1 = new Advanced('01234567890');
    $advanced1->fromArray($input1);
    $r['standard-1'] = [$advanced1, $input1];

    return $r;
});
