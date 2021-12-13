<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use VonageTest\VonageTestCase;
use Vonage\Voice\Filter\VoiceFilter;

uses(VonageTestCase::class);

/**
 * @throws Exception
 */
test('query has start date', function () {
    $filter = new VoiceFilter();
    $filter->setDateStart(new DateTimeImmutable('2020-01-01', new DateTimeZone('Z')));
    $query = $filter->getQuery();

    expect($query['date_start'])->toBe('2020-01-01T00:00:00Z');
});

/**
 * @throws Exception
 */
test('query has end date', function () {
    $query = (new VoiceFilter())
        ->setDateEnd(new DateTimeImmutable('2020-01-01', new DateTimeZone('Z')))
        ->getQuery();

    expect($query['date_end'])->toBe('2020-01-01T00:00:00Z');
});

test('query has conversation u u i d', function () {
    $query = (new VoiceFilter())
        ->setConversationUUID('CON-c39bc0bb-7ebc-405f-801b-f6b9a0d92860')
        ->getQuery();

    expect($query['conversation_uuid'])->toBe('CON-c39bc0bb-7ebc-405f-801b-f6b9a0d92860');
});

test('can set record index', function () {
    $query = (new VoiceFilter())
        ->setRecordIndex(100)
        ->getQuery();

    expect($query['record_index'])->toBe(100);
});

test('can set page size', function () {
    $query = (new VoiceFilter())
        ->setPageSize(100)
        ->getQuery();

    expect($query['page_size'])->toBe(100);
});

test('can set order', function () {
    $query = (new VoiceFilter())
        ->setOrder(VoiceFilter::ORDER_ASC)
        ->getQuery();

    expect($query['order'])->toBe(VoiceFilter::ORDER_ASC);
});

test('filter throw exception on bad order', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Order must be `asc` or `desc`');

    (new VoiceFilter())->setOrder('foo');
});

test('start date timezone is switched to u t c', function () {
    $filter = new VoiceFilter();
    $filter->setDateStart(new DateTimeImmutable('2020-01-01', new DateTimeZone('America/New_York')));

    $startDate = $filter->getDateStart();
    expect($startDate->getTimezone()->getName())->toBe('Z');
});

test('end date timezone is switched to u t c', function () {
    $filter = new VoiceFilter();
    $filter->setDateEnd(new DateTimeImmutable('2020-01-01', new DateTimeZone('America/New_York')));

    $startDate = $filter->getDateEnd();
    expect($startDate->getTimezone()->getName())->toBe('Z');
});
