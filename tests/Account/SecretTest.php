<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Account\Secret;
use Vonage\InvalidResponseException;

uses(VonageTestCase::class);

/**
 * @throws InvalidResponseException
 */
beforeEach(function () {
    $this->secret = @Secret::fromApi([
        'id' => 'ad6dc56f-07b5-46e1-a527-85530e625800',
        'created_at' => '2017-03-02T16:34:49Z',
        '_links' => [
            'self' => [
                'href' => '/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800'
            ]
        ]
    ]);
});

test('rejects invalid data no id', function () {
    $this->expectException(InvalidResponseException::class);

    new Secret(['id' => 'abc']);
});

test('rejects invalid data no created at', function () {
    $this->expectException(InvalidResponseException::class);

    new Secret(['created_at' => '2017-03-02T16:34:49Z']);
});

test('object access', function () {
    $this->assertEquals('ad6dc56f-07b5-46e1-a527-85530e625800', $this->secret->getId());
    $this->assertEquals('2017-03-02T16:34:49Z', $this->secret->getCreatedAt());
});

test('array access', function () {
    $this->assertEquals('ad6dc56f-07b5-46e1-a527-85530e625800', @$this->secret['id']);
    $this->assertEquals('2017-03-02T16:34:49Z', @$this->secret['created_at']);
});
