<?php

namespace Corpus\Di;

use Corpus\Di\Exceptions\InvalidArgumentException;
use Corpus\Di\Exceptions\RuntimeException;
use Corpus\Di\Exceptions\UndefinedIdentifierException;

class Di implements DiInterface {

	/** @var array<string,callable|object|string> The map in which to store our objects */
	protected array $map = [];

	/** @var array<string,mixed> The map in which we store called values */
	protected array $memoizedCallResults = [];

	public function getMany( array $ids ) : array {
		$return = [];
		foreach( $ids as $id ) {
			$return[] = $this->get($id);
		}

		return $return;
	}

	public function get( string $id ) : mixed {
		if( !$this->has($id) ) {
			throw new UndefinedIdentifierException("{$id} does not exist.");
		}

		if( array_key_exists($id, $this->memoizedCallResults) ) {
			return $this->memoizedCallResults[$id];
		}

		return $this->memoizedCallResults[$id] = $this->getNew($id);
	}

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

	public function getNew( string $id, array $args = [] ) : mixed {
		if( !array_key_exists($id, $this->map) ) {
			throw new UndefinedIdentifierException("{$id} does not exist.");
		}

		$entry = $this->map[$id];

		return match( true ) {
			is_callable($entry) => $this->callFromReflectiveParams($entry, $args),
			is_object($entry) => $entry,
			is_string($entry) && class_exists($entry) => $this->constructFromReflectiveParams($entry, $args),
			default => throw new RuntimeException('unhandled di entry type'),
		};
	}

	public function duplicate( string $src, string $dest ) : callable|object|string {
		return $this->set($dest, $this->raw($src));
	}

	public function set( string $id, mixed $value ) : callable|object|string {
		switch( true ) {
			case is_object($value):
			case is_callable($value):
			case is_string($value): // Do not check if a string is a class here. It triggers the autoloader.
				return $this->map[$id] = $value;
		}

		throw new InvalidArgumentException("Entries in Di must be a callable, a class name as a string, or an existing instance of an object.");
	}

	public function has( string $id ) : bool {
		return array_key_exists($id, $this->map);
	}

	public function raw( string $id ) : callable|object|string {
		if( isset($this->map[$id]) ) {
			return $this->map[$id];
		}

		throw new UndefinedIdentifierException("{$id} does not exist.");
	}

	/**
	 * @return mixed[]
	 */
	protected function getReflectiveDiMethodParams( \ReflectionFunctionAbstract $ref, array $initials = [] ) : array {
		$cParams   = $ref->getParameters();
		$arguments = [];
		foreach( $cParams as $cIndex => $cParam ) {
			$name    = $cParam->getName();
			$initial = $initials[$cIndex] ?? $initials[$name] ?? null;
			if( $initial !== null ) {
				$arguments[] = $initial;

				continue;
			}

			if( $cParam->isOptional() ) {
				$arguments[] = $cParam->getDefaultValue();

				continue;
			}

			$arguments[] = $this->get($name);
		}

		return $arguments;
	}

	public function constructFromReflectiveParams( string $className, array $initials = [] ) : object {
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

	public function callFromReflectiveParams( callable $callable, array $initials = [] ) : mixed {
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
