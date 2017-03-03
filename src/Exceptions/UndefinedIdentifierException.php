<?php

namespace Corpus\Di\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Class UndefinedIdentifierException
 *
 * Thrown when attempting to retrieve a key that does not exist.
 *
 * @package Corpus\Di\Exceptions
 */
class UndefinedIdentifierException extends \Exception implements NotFoundExceptionInterface {

}
