<?php

namespace RebelCode\CommandInvoker;

use Traversable;
use InvalidArgumentException;
use OutOfRangeException;
use DomainException;
use Exception as RootException;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Functionality for custom functions.
 *
 * @since [*next-version*]
 */
trait CustomFunctionsTrait
{
    /**
     * The map of codes to functions.
     *
     * @since [*next-version*]
     *
     * @var callable[]
     */
    protected $customFunctions;

    /**
     * Registers a custom function.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $code     The code to register the function with.
     * @param callable          $function The function to register.
     */
    protected function _registerCustomFunction($code, callable $function)
    {
        $code = $this->_normalizeFunctionCode($code);

        $this->customFunctions[$code] = $function;
    }

    /**
     * Registers multiple custom functions in bulk.
     *
     * @since [*next-version*]
     *
     * @param callable[]|Traversable $map A map of codes to function names.
     *
     * @throws InvalidArgumentException If the map, or a code or function in it
     *                                  is illegal.
     */
    protected function _registerCustomFunctions($map)
    {
        if (!is_array($map) && !($map instanceof Traversable)) {
            throw $this->_createInvalidArgumentException($this->__('Invalid custom functions map'), null, null, $map);
        }

        foreach ($map as $_code => $_function) {
            $this->_registerCustomFunction($_code, $_function);
        }
    }

    /**
     * Unregisters a custom function.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $code The code of the function to unregister.
     *
     * @throws OutOfRangeException      If no function with the specified code is registered.
     * @throws InvalidArgumentException If function code is illegal.
     */
    protected function _unregisterCustomFunction($code)
    {
        $code = $this->_normalizeFunctionCode($code);

        if (!isset($this->customFunctions[$code])) {
            throw $this->_createOutOfRangeException($this->__('No function with this code is defined'), null, null, $code);
        }

        unset($this->customFunctions[$code]);
    }

    /**
     * Calls a custom function by its registered code.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $code The code of the function to call.
     * @param array             $args The arguments to pass to the function.
     *
     * @throws DomainException          If the function is not callable
     * @throws InvalidArgumentException If the  function code is illegal.
     * @throws OutOfRangeException      If no function with the specified code is defined.
     * @throws DomainException          If the function is not callable.
     *
     * @return mixed The result of the function call.
     */
    protected function _callCustomFunction($code, array $args = [])
    {
        $code     = $this->_normalizeFunctionCode($code);
        $function = $this->_getCustomFunction($code);

        if (!is_callable($function)) {
            throw $this->_createDomainException($this->__('Function "%1$s" must be callable', [$code]), null, null, $function);
        }

        return call_user_func_array($function, $args);
    }

    /**
     * Retrieves a custom function by code.
     *
     * @since [*next-version*]
     *
     * @param string $code The code of the function to retrieve.
     *
     * @throws OutOfRangeException      If no function with the specified code is defined.
     * @throws InvalidArgumentException If the code is illegal.
     *
     * @return callable The custom function.
     */
    protected function _getCustomFunction($code)
    {
        $code = $this->_normalizeFunctionCode($code);

        if (!isset($this->customFunctions[$code])) {
            throw $this->_createOutOfRangeException($this->__('No function with this code is defined'), null, null, $code);
        }

        return $this->customFunctions[$code];
    }

    /**
     * Checks if a function with the specified code is registered.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $code The code to check for.
     *
     * @throws InvalidArgumentException If the function code is illegal.
     *
     * @return bool True if a function with the specified code is registered;
     *              otherwise false.
     */
    protected function _hasCustomFunction($code)
    {
        $code = $this->_normalizeFunctionCode($code);

        return isset($this->customFunctions[$code]);
    }

    /**
     * Normalizes a function code.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $code The function code to normalize.
     *
     * @throws InvalidArgumentException If code is illegal.
     *
     * @return string The normalized function code.
     */
    protected function _normalizeFunctionCode($code)
    {
        $code = $this->_normalizeString($code);
        $code = trim($code);

        return $code;
    }

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param Stringable|string|int|float|bool $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);

    /**
     * Creates a new out of range exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $key      The key that is out of range, if any.
     *
     * @return OutOfRangeException The new exception.
     */
    abstract protected function _createOutOfRangeException(
            $message = null,
            $code = null,
            RootException $previous = null,
            $key = null
    );

    /**
     * Creates a new domain exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $value    The value that is outside of the data domain, if any.
     *
     * @return DomainException The new exception.
     */
    abstract protected function _createDomainException(
            $message = null,
            $code = null,
            RootException $previous = null,
            $value = null
    );

    /**
     * Creates a new invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
            $message = null,
            $code = null,
            RootException $previous = null,
            $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see sprintf()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
