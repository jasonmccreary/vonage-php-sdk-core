<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

use Laminas\Diactoros\Response;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Message\Message;
use Vonage\Message\Text;

beforeEach(function () {
    $this->message = new Message($this->to, $this->from, [
        'text' => $this->text
    ]);
});

afterEach(function () {
    $this->message = null;
});

/**
 * Creating a new message, should result in the correct (matching) parameters.
 *
 * @throws ClientException
 */
test('required params', function () {
    $params = @$this->message->getRequestData();

    expect($params['to'])->toEqual($this->to);
    expect($params['from'])->toEqual($this->from);
});

/**
 * Optional params shouldn't be in the response, unless set.
 *
 * @throws ClientException
 */
test('no default params', function () {
    $params = array_keys(@$this->message->getRequestData());
    $diff = array_diff($params, $this->set); // should be no difference

    $this->assertEmpty($diff, 'message params contain unset values (could change default behaviour)');
});

/**
 * Common optional params can be set
 *
 *
 * @param $setter
 * @param $param
 * @param $values
 *
 * @throws ClientException
 */
test('optional params', function ($setter, $param, $values) {
    //check no default value
    $params = @$this->message->getRequestData();

    $this->assertArrayNotHasKey($param, $params);

    //test values
    foreach ($values as $value => $expected) {
        $this->message->$setter($value);
        $params = @$this->message->getRequestData();

        $this->assertArrayHasKey($param, $params);
        expect($params[$param])->toEqual($expected);
    }
})->with('optionalParams');

/**
 * Throw an exception when we make a call on a method that cannot change after request
 *
 *
 * @param $method
 * @param $argument
 */
test('can not change creation after response', function ($method, $argument) {
    $this->expectException('RuntimeException');

    $data = ['test' => 'test'];
    $response = new Response();
    $response->getBody()->write(json_encode($data));

    @$this->message->setResponse($response);
    $this->message->$method($argument);
})->with('responseMethodChangeList');

// Datasets
/**
 * @return array[]
 */
dataset('optionalParams', [
    ['requestDLR', 'status-report-req', [true => 1, false => 0]],
    ['setClientRef', 'client-ref', ['test' => 'test']],
    ['setCallback', 'callback', ['http://example.com/test-callback' => 'http://example.com/test-callback']],
    ['setNetwork', 'network-code', ['test' => 'test']],
    ['setTTL', 'ttl', ['1' => 1]],
    ['setClass', 'message-class', [Text::CLASS_FLASH => Text::CLASS_FLASH]],
]);

/**
 * Returns a series of methods/args to test on a Message object
 */
dataset('responseMethodChangeList', [
    ['requestDLR', true],
    ['setCallback', 'https://example.com/changed'],
    ['setClientRef', 'my-personal-message'],
    ['setNetwork', '1234'],
    ['setTTL', 3600],
    ['setClass', 0],
]);
