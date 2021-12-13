<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Vonage\Call\Filter;
use Vonage\Conversations\Conversation;

beforeEach(function () {
    $this->filter = @new Filter();
});

test('conversation', function () {
    $this->filter->setConversation('test');
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('conversation_uuid', $query);
    expect($query['conversation_uuid'])->toEqual('test');

    $this->filter->setConversation(new Conversation('test'));
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('conversation_uuid', $query);
    expect($query['conversation_uuid'])->toEqual('test');
});

test('status', function () {
    $this->filter->setStatus('test');
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('status', $query);
    expect($query['status'])->toEqual('test');
});

test('start', function () {
    $date = new DateTime('2018-03-31 11:33:42+00:00');
    $this->filter->setStart($date);
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('date_start', $query);
    expect($query['date_start'])->toEqual('2018-03-31T11:33:42Z');
});

test('start other timezone', function () {
    $date = new DateTime('2018-03-31 11:33:42-03:00');
    $this->filter->setStart($date);
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('date_start', $query);
    expect($query['date_start'])->toEqual('2018-03-31T14:33:42Z');
});

test('end', function () {
    $date = new DateTime('2018-03-31 11:33:42+00:00');
    $this->filter->setEnd($date);
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('date_end', $query);
    expect($query['date_end'])->toEqual('2018-03-31T11:33:42Z');
});

test('end other timezone', function () {
    $date = new DateTime('2018-03-31 11:33:42+03:00');
    $this->filter->setEnd($date);
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('date_end', $query);
    expect($query['date_end'])->toEqual('2018-03-31T08:33:42Z');
});

test('size', function () {
    $this->filter->setSize(1);
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('page_size', $query);
    expect($query['page_size'])->toEqual(1);
});

test('index', function () {
    $this->filter->setIndex(1);
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('record_index', $query);
    expect($query['record_index'])->toEqual(1);
});

test('order', function () {
    $this->filter->setOrder('asc');
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('order', $query);
    expect($query['order'])->toEqual('asc');
});

test('asc', function () {
    $this->filter->sortAscending();
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('order', $query);
    expect($query['order'])->toEqual('asc');
});

test('desc', function () {
    $this->filter->sortDescending();
    $query = $this->filter->getQuery();

    $this->assertArrayHasKey('order', $query);
    expect($query['order'])->toEqual('desc');
});
