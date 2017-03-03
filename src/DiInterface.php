<?php

namespace Corpus\Di;

use Psr\Container\ContainerInterface;

/**
 * Corpus Di Dependency Injection Container
 *
 * @author Jon Henderson
 * @author Jesse Donat
 * @package Corpus\Di
 */
interface DiInterface extends ContainerInterface {
	/**
	 * Retrieve multiple item. For use with list()
	 *
	 * @param array[] $data The array of (names/keys / argument) pair tuple of the items
	 * @return mixed[]
	 * @throws \InvalidArgumentException
	 */
	public function getManyNew( array $data );

	/**
	 * Retrieve multiple item; cached if existing. For use with list()
	 *
	 * @param string[] $names The names/keys of the items
	 * @return mixed[]
	 */
	public function getMany( array $names );

	/**
	 * Retrieve an item
	 *
	 * @param string  $name The name/key of the item
	 * @param mixed[] $args
	 * @return mixed
	 * @throws Exceptions\UndefinedIdentifierException
	 */
	public function getNew( $name, array $args = [] );

	/**
	 * Store a value via key to retrieve later
	 *
	 * @param string $name The name/key of the item
	 * @param mixed  $value The value to store
	 * @return mixed
	 */
	public function set( $name, $value );

	/**
	 * Clone a given value into a second key
	 *
	 * @param string $src The source
	 * @param string $dest The destination
	 * @return mixed
	 */
	public function duplicate( $src, $dest );

	/**
	 * @param string $id The name/key to be retrieved
	 * @return mixed
	 * @throws Exceptions\UndefinedIdentifierException
	 */
	public function raw( $id );
}