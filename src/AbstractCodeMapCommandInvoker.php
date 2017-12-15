<?php

namespace RebelCode\CommandInvoker;

abstract class AbstractCodeMapCommandInvoker
{
    /* Adds functionality for adding, removing, checking, and invoking custom functions by code.
     *
     * @since [*next-version*]
     */
    use CustomFunctionsTrait;

    /**
     * Parameter-less constructor.
     *
     * Invoke this in actual constructor.
     *
     * @since [*next-version*]
     */
    protected function _construct()
    {
    }
}
