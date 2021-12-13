<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use DateTime;
use VonageTest\VonageTestCase;
use Vonage\Voice\Message\Callback;

uses(VonageTestCase::class);

beforeEach(function () {
    $this->callback = new Callback($this->data);
});

test('simple values', function () {
    $this->assertEquals($this->data['call-id'], $this->callback->getId());
    $this->assertEquals($this->data['status'], $this->callback->getStatus());
    $this->assertEquals($this->data['call-price'], $this->callback->getPrice());
    $this->assertEquals($this->data['call-rate'], $this->callback->getRate());
    $this->assertEquals($this->data['call-duration'], $this->callback->getDuration());
    $this->assertEquals($this->data['to'], $this->callback->getTo());
    $this->assertEquals($this->data['network-code'], $this->callback->getNetwork());
});

test('start and end optional', function () {
    unset($this->data['call-start'], $this->data['call-end']);

    $this->callback = new Callback($this->data);

    $this->assertNull($this->callback->getStart());
    $this->assertNull($this->callback->getEnd());
});

test('date values', function () {
    $this->assertEquals(new DateTime('2014-01-01 10:30:15'), $this->callback->getCreated());
    $this->assertEquals(new DateTime('2014-01-01 10:30:25'), $this->callback->getStart());
    $this->assertEquals(new DateTime('2014-01-01 10:30:35'), $this->callback->getEnd());
});
