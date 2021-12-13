<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Vonage\Voice\Message\Callback;

beforeEach(function () {
    $this->callback = new Callback($this->data);
});

test('simple values', function () {
    expect($this->callback->getId())->toEqual($this->data['call-id']);
    expect($this->callback->getStatus())->toEqual($this->data['status']);
    expect($this->callback->getPrice())->toEqual($this->data['call-price']);
    expect($this->callback->getRate())->toEqual($this->data['call-rate']);
    expect($this->callback->getDuration())->toEqual($this->data['call-duration']);
    expect($this->callback->getTo())->toEqual($this->data['to']);
    expect($this->callback->getNetwork())->toEqual($this->data['network-code']);
});

test('start and end optional', function () {
    unset($this->data['call-start'], $this->data['call-end']);

    $this->callback = new Callback($this->data);

    expect($this->callback->getStart())->toBeNull();
    expect($this->callback->getEnd())->toBeNull();
});

test('date values', function () {
    expect($this->callback->getCreated())->toEqual(new DateTime('2014-01-01 10:30:15'));
    expect($this->callback->getStart())->toEqual(new DateTime('2014-01-01 10:30:25'));
    expect($this->callback->getEnd())->toEqual(new DateTime('2014-01-01 10:30:35'));
});
