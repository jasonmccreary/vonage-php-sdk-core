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

uses(VonageTestCase::class);

/**
 *
 * @param $advanced
 * @param $inputData
 */
test('array access', function ($advanced, $inputData) {
    $this->assertEquals($inputData['valid_number'], @$advanced['valid_number']);
    $this->assertEquals($inputData['reachable'], @$advanced['reachable']);
})->with('advancedTestProvider');

/**
 *
 * @param $advanced
 * @param $inputData
 */
test('object access', function ($advanced, $inputData) {
    $this->assertEquals($inputData['valid_number'], $advanced->getValidNumber());
    $this->assertEquals($inputData['reachable'], $advanced->getReachable());
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
