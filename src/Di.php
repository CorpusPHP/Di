<?php

namespace Corpus\Di;

use Corpus\Di\Exceptions\UndefinedIdentifierException;

class Di implements DiInterface {

	/**
	 * @var array The map in which to store our objects
	 */
	protected $map = array();

	/**
	 * @var array The map in which we store called values
	 */
	protected $callResultTable = array();

	public function getMany( array $names ) {
		$return = array();
		foreach( $names as $name ) {
			$return[] = $this->get($name);
		}

		return $return;
	}

	public function get( $name ) {

		if( !array_key_exists($name, $this->map) ) {
			throw new UndefinedIdentifierException("{$name} does not exist.");
		}

		if( !is_callable($this->map[$name]) ) {
			return $this->map[$name];
		}

		if( !array_key_exists($name, $this->callResultTable) ) {
			$this->callResultTable[$name] = call_user_func($this->map[$name]);
		}

		return $this->callResultTable[$name];
	}

	public function getManyNew( array $data ) {
		$return = array();
		foreach( $data as $pair ) {
			if( is_array($pair) ) {
				if( count($pair) == 2 ) {
					$return[] = $this->getNew($pair[0], $pair[1]);
				} elseif( count($pair) == 1 ) {
					$return[] = $this->getNew($pair[0]);
				} else {
					throw new \InvalidArgumentException('Argument should be an array of pair tuple or string');
				}
			} elseif( is_string($pair) ) {
				$return[] = $this->getNew($pair);
			} else {
				throw new \InvalidArgumentException('Argument should be an array of pair tuple or string');
			}
		}

		return $return;
	}

	public function getNew( $name, array $args = array() ) {

		if( !array_key_exists($name, $this->map) ) {
			throw new UndefinedIdentifierException("{$name} does not exist.");
		}

		if( !is_callable($this->map[$name]) ) {
			return $this->map[$name];
		}

		return call_user_func_array($this->map[$name], $args);

	}

	public function duplicate( $src, $dest ) {
		return $this->set($dest, $this->raw($src));
	}

	public function set( $name, $value ) {
		return $this->map[$name] = $value;
	}

	public function raw( $name ) {
		if( isset($this->map[$name]) ) {
			return $this->map[$name];
		}

		throw new UndefinedIdentifierException("{$name} does not exist.");
	}
}