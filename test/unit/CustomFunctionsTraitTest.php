<?php

namespace RebelCode\CommandInvoker\UnitTest;

use Xpmock\TestCase;
use RebelCode\CommandInvoker\CustomFunctionsTrait as TestSubject;
use Dhii\Util\String\StringableInterface as Stringable;
use PHPUnit_Framework_MockObject_MockObject;
use InvalidArgumentException;
use OutOfRangeException;
use DomainException;
use ArrayIterator;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class CustomFunctionsTraitTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\CommandInvoker\CustomFunctionsTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance($methods = [])
    {
        $methods = $this->mergeValues($methods, [
            '_createInvalidArgumentException',
            '_createOutOfRangeException',
            '_createDomainException',
            '__',
        ]);
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
            ->setMethods($methods)
            ->getMockForTrait();

        $mock->method('_createInvalidArgumentException')
            ->will($this->returnCallback(function ($message) {
                return new InvalidArgumentException($message);
            }));
        $mock->method('_createOutOfRangeException')
            ->will($this->returnCallback(function ($message) {
                return new OutOfRangeException($message);
            }));
        $mock->method('_createDomainException')
            ->will($this->returnCallback(function ($message) {
                return new DomainException($message);
            }));
        $mock->method('__')
            ->will($this->returnCallback(function ($string) {
                return $string;
            }));

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Creates a stringable.
     *
     * @since [*next-version*]
     *
     * @param string $string The string that the stringable should represent.
     *
     * @return Stringable The new stringable
     */
    public function createStringable($string = '')
    {
        $mock = $this->getMock('Dhii\Util\String\StringableInterface');
        $mock->method('__toString')
            ->will($this->returnCallback(function () use ($string) {
                return $string;
            }));

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests that `_registerCustomFunction()` method works as expected.
     *
     * @since [*next-version*]
     */
    public function testRegisterCustomFunction()
    {
        $code = uniqid('string-');
        $stringable = $this->createStringable($code);
        $command = function () {
        };
        $subject = $this->createInstance(['_normalizeFunctionCode']);
        $subject->expects($this->exactly(1))
            ->method('_normalizeFunctionCode')
            ->with($this->equalTo($stringable))
            ->will($this->returnCallback(function ($code) {
                return (string) $code;
            }));
        $_subject = $this->reflect($subject);

        $_subject->_registerCustomFunction($stringable, $command);
        $result = $_subject->customFunctions;
        $this->assertArrayHasKey($code, $result, 'The custom function was not registered');
        $this->assertSame($command, $result[$code], 'Wrong custom function registered');
    }

    /**
     * Tests that `_registerCustomFunction()` method fails as expected when given an invalid function.
     *
     * @since [*next-version*]
     */
    public function testRegisterCustomFunctionFailureNotCallable()
    {
        $code = uniqid('string-');
        $stringable = $this->createStringable($code);
        $command = rand(1, 99);
        $subject = $this->createInstance();

        $this->setExpectedException('InvalidArgumentException');
        $_subject = $this->reflect($subject);

        $_subject->_registerCustomFunction($stringable, $command);
    }

    /**
     * Tests that `_registerCustomFunctions()` method works as expected.
     *
     * @since [*next-version*]
     */
    public function testRegisterCustomFunctions()
    {
        $subject = $this->createInstance(['_registerCustomFunction']);
        $_subject = $this->reflect($subject);
        $funcs = [
            uniqid('key-') => function () {
            },
            uniqid('key-') => function () {
            },
        ];
        $funcsKeys = array_keys($funcs);
        $funcMap = new ArrayIterator($funcs);

        $subject->expects($this->exactly(count($funcs)))
            ->method('_registerCustomFunction')
            ->withConsecutive(
                [$funcsKeys[0], $funcs[$funcsKeys[0]]],
                [$funcsKeys[1], $funcs[$funcsKeys[1]]]
            );

        $_subject->_registerCustomFunctions($funcMap);
    }

    /**
     * Tests that `_registerCustomFunctions()` method fails as expected when given wrong map.
     *
     * @since [*next-version*]
     */
    public function testRegisterCustomFunctionsFailureTraversable()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $funcMap = uniqid('map-');

        $this->setExpectedException('InvalidArgumentException');
        $_subject->_registerCustomFunctions($funcMap);
    }

    /**
     * Tests that `_unregisterCustomFunction()` method works as expected.
     *
     * @since [*next-version*]
     */
    public function testUnregisterCustomFunction()
    {
        $subject = $this->createInstance(['_normalizeFunctionCode']);
        $_subject = $this->reflect($subject);

        $code = uniqid('code-');
        $func = function () {
        };

        $subject->method('_normalizeFunctionCode')
            ->will($this->returnCallback(function ($code) {
                return (string) $code;
            }));

        $_subject->customFunctions = [$code => $func];
        $_subject->_unregisterCustomFunction($code);

        $this->assertArrayNotHasKey($code, $_subject->customFunctions, 'Modified state of function map is wrong');
    }

    /**
     * Tests that `_unregisterCustomFunction()` method fails as expected when given a non-existing code.
     *
     * @since [*next-version*]
     */
    public function testUnregisterCustomFunctionFailureNoSuchCode()
    {
        $subject = $this->createInstance(['_normalizeFunctionCode']);
        $_subject = $this->reflect($subject);

        $code = uniqid('code-');
        $func = function () {};

        $subject->method('_normalizeFunctionCode')
            ->will($this->returnCallback(function ($code) {
                return (string) $code;
            }));

        $_subject->customFunctions = [uniqid('code-') => $func];

        $this->setExpectedException('OutOfRangeException');
        $_subject->_unregisterCustomFunction($code);
    }

    /**
     * Tests that `_callCustomFunction()` method works as expected.
     *
     * @since [*next-version*]
     */
    public function testCallCustomFunction()
    {
        $subject = $this->createInstance(['_normalizeFunctionCode', '_getCustomFunction']);
        $_subject = $this->reflect($subject);

        $sep = uniqid();
        $value = uniqid('value-');
        $arg = $value;
        $code = uniqid('code-');
        $algo = function ($arg) use ($sep) { return implode($sep, $arg); };
        $func = function ($arg) use ($algo) { return $algo($arg); };
        $args = [uniqid(), uniqid()];

        $subject->method('_normalizeFunctionCode')
            ->will($this->returnCallback(function ($code) {
                return (string) $code;
            }));

        $subject->expects($this->exactly(1))
            ->method('_getCustomFunction')
            ->with($code)
            ->will($this->returnValue($func));

        $result = $_subject->_callCustomFunction($code, [$args]);
        $this->assertEquals($algo($args), $result, 'Called function did not return expected value');
    }

    /**
     * Tests that the `_callCustomFunction()` method fails correctly when given invalid function arguments.
     *
     * @since [*next-version*]
     */
    public function testCallCustomFunctionFailureInvalidArgs()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');
        $_subject->_callCustomFunction(uniqid(), uniqid());
    }

    /**
     * Tests that the `_callCustomFunction()` method fails correctly when custom function is not callable.
     *
     * @since [*next-version*]
     */
    public function testCallCustomFunctionFailureInvalidFunction()
    {
        $subject = $this->createInstance(['_getCustomFunction', '_normalizeFunctionCode']);
        $_subject = $this->reflect($subject);

        $code = uniqid('code-');
        $func = uniqid('function-');
        $args = [];

        $subject->method('_normalizeFunctionCode')
            ->will($this->returnCallback(function ($code) {
                return (string) $code;
            }));

        $subject->expects($this->exactly(1))
            ->method('_getCustomFunction')
            ->with($code)
            ->will($this->returnValue($func));

        $this->setExpectedException('DomainException');
        $_subject->_callCustomFunction($code, $args);
    }

    /**
     * Tests that the `_getCustomFunction()` method works as expected.
     *
     * @since [*next-version*]
     */
    public function testGetCustomFunction()
    {
        $subject = $this->createInstance(['_normalizeFunctionCode']);
        $_subject = $this->reflect($subject);

        $code = uniqid('func-');
        $func = function () {};
        $funcs = [$code => $func];

        $_subject->customFunctions = $funcs;
        $subject->method('_normalizeFunctionCode')
            ->will($this->returnCallback(function ($code) {
                return (string) $code;
            }));

        $result = $_subject->_getCustomFunction($code);
        $this->assertSame($func, $result, 'The retrieved function is wrong');
    }

    /**
     * Tests that the `_getCustomFunction()` method fails as expected when given a non-existing code.
     *
     * @since [*next-version*]
     */
    public function testGetCustomFunctionFailureCodeNotExists()
    {
        $subject = $this->createInstance(['_normalizeFunctionCode']);
        $_subject = $this->reflect($subject);

        $_subject->customFunctions = [];
        $subject->method('_normalizeFunctionCode')
            ->will($this->returnCallback(function ($code) {
                return (string) $code;
            }));

        $this->setExpectedException('OutOfRangeException');
        $result = $_subject->_getCustomFunction(uniqid());
    }

    /**
     * Tests that the `_hasCustomFunction()` method correctly reports existence of function.
     *
     * @since [*next-version*]
     */
    public function testHasCustomFunction()
    {
        $subject = $this->createInstance(['_normalizeFunctionCode']);
        $_subject = $this->reflect($subject);

        $code = uniqid('code-');
        $func = function () {
        };
        $funcs = [$code => $func];

        $_subject->customFunctions = $funcs;
        $subject->method('_normalizeFunctionCode')
            ->will($this->returnCallback(function ($code) {
                return (string) $code;
            }));

        $result = $_subject->_hasCustomFunction($code);
        $this->assertTrue($result, 'Wrongly reported that no function with specified code exists');

        $result = $_subject->_hasCustomFunction(uniqid());
        $this->assertFalse($result, 'Wrongly reported that a function with specified code exists');
    }
}
