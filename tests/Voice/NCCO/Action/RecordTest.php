<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\Action\Record;


test('webhook method can be set in factory', function () {
    $action = Record::factory([
        'eventUrl' => 'https://test.domain/recording',
        'eventMethod' => 'GET'
    ]);

    expect($action->getEventWebhook()->getMethod())->toBe('GET');
    expect($action->toNCCOArray()['eventMethod'])->toBe('GET');
});

test('json serialize looks correct', function () {
    $this->assertSame([
        'action' => 'record',
        'format' => 'mp3',
        'timeOut' => '7200',
        'beepStart' => 'false'
    ], (new Record())->jsonSerialize());
});

test('setting channel back to one resets values', function () {
    $action = new Record();

    expect($action->getSplit())->toBeNull();
    expect($action->getChannels())->toBeNull();

    $action->setChannels(2);

    expect($action->getSplit())->toBe(Record::SPLIT);
    expect($action->getChannels())->toBe(2);

    $action->setChannels(1);

    expect($action->getSplit())->toBeNull();
    expect($action->getChannels())->toBeNull();
});

test('cannot set too many channels', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Number of channels must be 32 or less');

    (new Record())->setChannels(100);
});

test('cannot set invalid timeout', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('TimeOut value must be between 3 and 7200 seconds, inclusive');

    (new Record())->setTimeout(1);
});

test('cannot set invalid silence timeout', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('End On Silence value must be between 3 and 10 seconds, inclusive');

    (new Record())->setEndOnSilence(1);
});

test('cannot set invalid end on key', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid End on Key character');

    (new Record())->setEndOnKey('h');
});

test('cannot set invalid split value', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Split value must be "conversation" if enabling');

    (new Record())->setSplit('foo');
});
