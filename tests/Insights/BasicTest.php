<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Insights\Basic;


/**
 *
 * @param $basic
 * @param $inputData
 */
test('array access', function ($basic, $inputData) {
    expect(@$basic['request_id'])->toEqual($inputData['request_id']);
    expect(@$basic['international_format_number'])->toEqual($inputData['international_format_number']);
    expect(@$basic['national_format_number'])->toEqual($inputData['national_format_number']);
    expect(@$basic['country_code'])->toEqual($inputData['country_code']);
    expect(@$basic['country_code_iso3'])->toEqual($inputData['country_code_iso3']);
    expect(@$basic['country_name'])->toEqual($inputData['country_name']);
    expect(@$basic['country_prefix'])->toEqual($inputData['country_prefix']);
})->with('basicTestProvider');

/**
 *
 * @param $basic
 * @param $inputData
 */
test('object access', function ($basic, $inputData) {
    expect($basic->getRequestId())->toEqual($inputData['request_id']);
    expect($basic->getInternationalFormatNumber())->toEqual($inputData['international_format_number']);
    expect($basic->getNationalFormatNumber())->toEqual($inputData['national_format_number']);
    expect($basic->getCountryCode())->toEqual($inputData['country_code']);
    expect($basic->getCountryCodeISO3())->toEqual($inputData['country_code_iso3']);
    expect($basic->getCountryName())->toEqual($inputData['country_name']);
    expect($basic->getCountryPrefix())->toEqual($inputData['country_prefix']);
})->with('basicTestProvider');

// Datasets
dataset('basicTestProvider', function () {
    $r = [];

    $inputBasic1 = [
            'status' => 0,
            'status_message' => 'Success',
            'request_id' => 'cc903ddb-4427-421b-8938-8b377cd76710',
            'international_format_number' => '447908123456',
            'national_format_number' => '07908 123456',
            'country_code' => 'GB',
            'country_code_iso3' => 'GBR',
            'country_name' => 'United Kingdom',
            'country_prefix' => 44,
    ];

    $basic1 = new Basic($inputBasic1['national_format_number']);
    $basic1->fromArray($inputBasic1);
    $r['basic-1'] = [$basic1, $inputBasic1];

    return $r;
});
