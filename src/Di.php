<?php

namespace Corpus\Di;

use Corpus\Di\Exceptions\InvalidArgumentException;
use Corpus\Di\Exceptions\RuntimeException;
use Corpus\Di\Exceptions\UndefinedIdentifierException;

class Di implements DiInterface {

	/**
	 * @var array The map in which to store our objects
	 */
	protected $map = [];

	/**
	 * @var array The map in which we store called values
	 */
	protected $memoizedCallResults = [];

	/**
	 * @inheritDoc
	 */
	public function getMany( array $ids ) : array {
		$return = [];
		foreach( $ids as $id ) {
			$return[] = $this->get($id);
		}

		return $return;
	}

	/**
	 * @inheritDoc
	 */
	public function get( $id ) {
		if( !$this->has($id) ) {
			throw new UndefinedIdentifierException("{$id} does not exist.");
		}

		if( array_key_exists($id, $this->memoizedCallResults) ) {
			return $this->memoizedCallResults[$id];
		}

		return $this->memoizedCallResults[$id] = $this->getNew($id);
	}

	/**
	 * @inheritDoc
	 */
	public function getManyNew( array $data ) : array {
		$return = [];
		foreach( $data as $pair ) {
			if( is_array($pair) ) {
				if( count($pair) == 2 ) {
					$return[] = $this->getNew($pair[0], $pair[1]);
				} elseif( count($pair) == 1 ) {
					$return[] = $this->getNew($pair[0]);
				} else {
					throw new InvalidArgumentException('Argument should be an array of pair tuple or string');
				}
			} elseif( is_string($pair) ) {
				$return[] = $this->getNew($pair);
			} else {
				throw new InvalidArgumentException('Argument should be an array of pair tuple or string');
			}
		}

		return $return;
	}

	/**
	 * @inheritDoc
	 */
	public function getNew( $id, array $args = [] ) {
		if( !array_key_exists($id, $this->map) ) {
			throw new UndefinedIdentifierException("{$id} does not exist.");
		}

		$entry = $this->map[$id];

		switch( true ) {
			case is_callable($entry):
				return $this->callFromReflectiveParams($entry, $args);
			case is_object($entry):
				return $entry;
			case class_exists($entry):
				return $this->constructFromReflectiveParams($entry, $args);
		}

		throw new RuntimeException('unhandled di entry type');
	}

	/**
	 * @inheritDoc
	 */
	public function duplicate( string $src, string $dest ) {
		return $this->set($dest, $this->raw($src));
	}

	/**
	 * @inheritDoc
	 */
	public function set( string $id, $value ) {
		switch( true ) {
			case is_object($value):
			case is_callable($value):
			case is_string($value): // don't check if it is a class until get as it triggers the autoloader
				return $this->map[$id] = $value;
		}

		throw new InvalidArgumentException("Entries in Di must be a callable, a class name as a string, or an existing instance of an object.");
	}

	/**
	 * @inheritDoc
	 */
	public function has( $id ) {
		return array_key_exists($id, $this->map);
	}

	/**
	 * @inheritDoc
	 */
	public function raw( string $id ) {
		if( isset($this->map[$id]) ) {
			return $this->map[$id];
		}

		throw new UndefinedIdentifierException("{$id} does not exist.");
	}

	/**
	 * @param \ReflectionFunction|\ReflectionMethod $ref
	 * @return array
	 */
	protected function getReflectiveDiMethodParams( $ref, array $initials = [] ) {
		/** @var \ReflectionParameter[] $cParams */
		$cParams   = array_slice($ref->getParameters(), count($initials));
		$arguments = $initials;
		foreach( $cParams as $cParam ) {
			if( !$cParam->isOptional() ) {
				$arguments[] = $this->get($cParam->getName());
			}
		}

		return $arguments;
	}

	/**
	 * @inheritdoc
	 */
	public function constructFromReflectiveParams( string $className, array $initials = [] ) {
		try {
			$inst = new \ReflectionClass($className);
			$ref  = $inst->getConstructor();
		} catch( \ReflectionException $ex ) {
			throw new InvalidArgumentException('reflection of callable failed', 3, $ex);
		}

		if( $ref instanceof \ReflectionMethod ) {
			$args = $this->getReflectiveDiMethodParams($ref, $initials);

			return $inst->newInstanceArgs($args);
		}

		return new $className;
	}

	/**
	 * @inheritdoc
	 */
	public function callFromReflectiveParams( callable $callable, array $initials = [] ) {
		try {
			if( is_array($callable) ) {
				$ref  = new \ReflectionMethod($callable[0], $callable[1]);
				$args = $this->getReflectiveDiMethodParams($ref, $initials);
			} elseif( is_string($callable) || $callable instanceof \closure ) {
				$ref  = new \ReflectionFunction($callable);
				$args = $this->getReflectiveDiMethodParams($ref, $initials);
			} elseif( is_object($callable) && method_exists($callable, '__invoke') ) {
				$ref  = new \ReflectionClass($callable);
				$meth = $ref->getMethod('__invoke');
				$args = $this->getReflectiveDiMethodParams($meth, $initials);
			} else {
				throw new InvalidArgumentException('reflection of callable failed', 1);
			}
		} catch( \ReflectionException $ex ) {
			throw new InvalidArgumentException('reflection of callable failed', 2, $ex);
		}

		return call_user_func_array($callable, $args);
	}
}
