<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Application\Filter;


/**
 *
 * @param $start
 * @param $end
 * @param $expected
 */
test('ranges', function ($start, $end, $expected) {
    $filter = new Filter($start, $end);

    expect($filter->getQuery())->toBeArray();
    $this->assertArrayHasKey('date', $filter->getQuery());
    expect($filter->getQuery()['date'])->toEqual($expected);
})->with('ranges');

// Datasets
/**
 * @return array[]
 */
dataset('ranges', [
    [
        new DateTime('March 10th 1983'),
        new DateTime('June 3rd 1982'),
        '1982:06:03:00:00:00-1983:03:10:00:00:00'
    ],
    [
        new DateTime('June 3rd 1982'),
        new DateTime('March 10th 1983'),
        '1982:06:03:00:00:00-1983:03:10:00:00:00'
    ],
    [
        new DateTime('Jan 1, 2016 10:44:33 PM'),
        new DateTime('Feb 1, 2016 5:45:12'),
        '2016:01:01:22:44:33-2016:02:01:05:45:12'
    ],
    [
        new DateTime('Feb 1, 2016 5:45:12'),
        new DateTime('Jan 1, 2016 10:44:33 PM'),
        '2016:01:01:22:44:33-2016:02:01:05:45:12'
    ],
]);
