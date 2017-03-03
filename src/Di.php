<?php

namespace Corpus\Di;

use Corpus\Di\Exceptions\InvalidArgumentException;
use Corpus\Di\Exceptions\UndefinedIdentifierException;

class Di implements DiInterface {

	use ReflectiveDiMethodParameterCallTrait;

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
	public function getMany( array $ids ) {
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
	public function getManyNew( array $data ) {
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
				$result = call_user_func_array($entry, $args);
				break;
			case is_object($entry):
				$result = $entry;
				break;
			case class_exists($entry):
				$result = $this->constructInstanceFromReflectiveDiMethodParams($this, $entry, $args);
				break;
			default:
				throw new \RuntimeException;
				break;
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function duplicate( $src, $dest ) {
		return $this->set($dest, $this->raw($src));
	}

	/**
	 * @inheritDoc
	 */
	public function set( $id, $value ) {
		switch( true ) {
			case is_object($value):
			case is_callable($value):
			case is_string($value): // don't check if it is a class until get as it triggers the autoloader
				return $this->map[$id] = $value;
				break;
			default:
				throw new InvalidArgumentException("Entries in Di must be a callable, a class name as a string, or an existing instance of an object.");
				break;
		}
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
	public function raw( $id ) {
		if( isset($this->map[$id]) ) {
			return $this->map[$id];
		}

		throw new UndefinedIdentifierException("{$id} does not exist.");
	}
}
