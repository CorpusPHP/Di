<?php

namespace Corpus\Di\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Class UndefinedIdentifierException
 *
 * Thrown when attempting to retrieve a key that does not exist.
 */
class UndefinedIdentifierException extends \OutOfBoundsException implements NotFoundExceptionInterface {

}
