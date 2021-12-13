<?php

uses(\VonageTest\VonageTestCase::class)->in('tests', 'Account', 'Application', 'Call', 'Client', 'Conversation', 'Conversion', 'Fixture', 'Insights', 'Logger', 'Message', 'Network', 'Numbers', 'Redact', 'Response', 'SMS', 'Secrets', 'Traits', 'User', 'Verify', 'Voice', 'responses');
uses(\VonageTest\Psr7AssertionTrait::class)->in('Conversation', 'Conversion', 'Redact', 'User');
uses(\VonageTest\HTTPTestTrait::class)->in('Secrets');

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

/** @link https://pestphp.com/docs/underlying-test-case */

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/** @link https://pestphp.com/docs/expectations#custom-expectations */

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/** @link https://pestphp.com/docs/helpers */
