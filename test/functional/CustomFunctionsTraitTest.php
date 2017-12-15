<?php

namespace RebelCode\CommandInvoker\FuncTest;

use Xpmock\TestCase;
use RebelCode\CommandInvoker\CustomFunctionsTrait as TestSubject;
use Dhii\Util\String\StringableInterface as Stringable;
use PHPUnit_Framework_MockObject_MockObject;
use InvalidArgumentException;
use OutOfRangeException;
use DomainException;

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
            '_normalizeString',
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
        $mock->method('_normalizeString')
            ->will($this->returnCallback(function ($string) {
                return (string) $string;
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
     * Tests that functions can be added, checked for, called, and removed consistently.
     *
     * @since [*next-version*]
     */
    public function testRegisterCheckCallUnregister()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $code = uniqid('string-');
        $stringable = $this->createStringable($code);
        $value = uniqid('value-');
        $command = function ($arg) { return $arg; };

        $isHas = $_subject->_hasCustomFunction($code);
        $this->assertFalse($isHas, 'Wrongly reported having the custom function in initial state');

        $_subject->_registerCustomFunction($stringable, $command);
        $isHas = $_subject->_hasCustomFunction($code);
        $this->assertTrue($isHas, 'Wrongly reported not having the custom function in modified state');

        $result = $_subject->_callCustomFunction($code, [$value]);
        $this->assertSame($value, $result, 'Calling custom function returned wrong result');

        $_subject->_unregisterCustomFunction($code);
        $isHas = $_subject->_hasCustomFunction($code);
        $this->assertFalse($isHas, 'Wrongly reported having the custom function in final state');
    }
}
