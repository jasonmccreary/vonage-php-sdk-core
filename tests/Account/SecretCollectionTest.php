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
use Vonage\Account\SecretCollection;
use Vonage\InvalidResponseException;



/**
 * @throws InvalidResponseException
 */
beforeEach(function () {
    $this->secrets = [
        [
            'id' => 'ad6dc56f-07b5-46e1-a527-85530e625800',
            'created_at' => '2017-03-02T16:34:49Z',
            '_links' => [
                'self' => [
                    'href' => '/accounts/abcd1234/secrets/ad6dc56f-07b5-46e1-a527-85530e625800'
                ]
            ]
        ]
    ];

    $this->links = [
        'self' => [
            'href' => '/accounts/abcd1234/secrets'
        ]
    ];

    $this->collection = new SecretCollection($this->secrets, $this->links);
});

test('get secrets', function () {
    $secrets = $this->collection->getSecrets();

    expect($secrets[0])->toBeInstanceOf(Secret::class);
});

test('get links', function () {
    $this->assertArrayHasKey('self', $this->collection->getLinks());
});

/**
 * @throws InvalidResponseException
 */
test('object access', function () {
    expect($this->collection->getLinks())->toEqual($this->links);

    $secrets = array_map(static function ($v) {
        return @Secret::fromApi($v);
    }, $this->secrets);

    expect($this->collection->getSecrets())->toEqual($secrets);
});

/**
 * @throws InvalidResponseException
 */
test('array access', function () {
    expect(@$this->collection['_links'])->toEqual($this->links);

    $secrets = array_map(static function ($v) {
        return @Secret::fromApi($v);
    }, $this->secrets);

    expect(@$this->collection['secrets'])->toEqual($secrets);
});
