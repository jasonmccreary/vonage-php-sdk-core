<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;

uses(VonageTestCase::class);

/**
 *
 * @param $cnam
 * @param $inputData
 */
test('array access', function ($cnam, $inputData) {
    expect(@$cnam['first_name'])->toEqual($inputData['first_name']);
    expect(@$cnam['last_name'])->toEqual($inputData['last_name']);
    expect(@$cnam['caller_name'])->toEqual($inputData['caller_name']);
    expect(@$cnam['caller_type'])->toEqual($inputData['caller_type']);
})->with('cnamProvider');

/**
 *
 * @param $cnam
 * @param $inputData
 */
test('object access', function ($cnam, $inputData) {
    expect($cnam->getFirstName())->toEqual($inputData['first_name']);
    expect($cnam->getLastName())->toEqual($inputData['last_name']);
    expect($cnam->getCallerName())->toEqual($inputData['caller_name']);
    expect($cnam->getCallerType())->toEqual($inputData['caller_type']);
})->with('cnamProvider');

// Datasets
dataset('cnamProvider', function () {
    $r = [];

    $input1 = [
        'first_name' => 'Tony',
        'last_name' => 'Tiger',
        'caller_name' => 'Tony Tiger Esq',
        'caller_type' => 'consumer'
    ];

    $cnam1 = new Cnam('14155550100');
    $cnam1->fromArray($input1);
    $r['cnam-1'] = [$cnam1, $input1];

    return $r;
});
