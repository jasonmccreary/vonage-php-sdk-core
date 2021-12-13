<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Message\Shortcode;
use Vonage\Message\Shortcode\Alert;
use Vonage\Message\Shortcode\Marketing;
use Vonage\Message\Shortcode\TwoFactor;

uses(VonageTestCase::class);

/**
 *
 * @param $klass
 * @param $expectedType
 */
test('type', function ($klass, $expectedType) {
    $m = new $klass('14155550100');

    expect($m->getType())->toEqual($expectedType);
})->with('typeProvider');

/**
 *
 * @param $expected
 * @param $type
 *
 * @throws ClientException
 */
test('create message from array', function ($expected, $type) {
    $message = Shortcode::createMessageFromArray(['type' => $type, 'to' => '14155550100']);
    expect($message)->toBeInstanceOf($expected);
})->with('typeProvider');

test('get request data', function () {
    $m = new TwoFactor("14155550100", ['link' => 'https://example.com'], ['status-report-req' => 1]);
    $actual = $m->getRequestData();

    $this->assertEquals([
        'to' => '14155550100',
        'link' => 'https://example.com',
        'status-report-req' => 1
    ], $actual);
});

// Datasets
/**
 * @return string[]
 */
dataset('typeProvider', [
    [TwoFactor::class, '2fa'],
    [Marketing::class, 'marketing'],
    [Alert::class, 'alert']
]);
