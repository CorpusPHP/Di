<?php

namespace Corpus\Di;

use Psr\Container\ContainerInterface;

/**
 * Corpus Di Dependency Injection Container
 *
 * @author Jon Henderson
 * @author Jesse Donat
 */
interface DiInterface extends ContainerInterface {

	/**
	 * Retrieve multiple item. For use with list()
	 *
	 * @param array[] $data The array of (names/keys / argument) pair tuple of the items
	 * @throws \InvalidArgumentException
	 * @return mixed[]
	 */
	public function getManyNew( array $data ) : array;

	/**
	 * Retrieve multiple item; cached if existing. For use with list()
	 *
	 * @param string[] $ids The names/keys of the items
	 * @return mixed[]
	 */
	public function getMany( array $ids ) : array;

	/**
	 * Retrieve an item
	 *
	 * @param string  $id   The name/key of the item
	 * @param mixed[] $args
	 * @throws \Corpus\Di\Exceptions\UndefinedIdentifierException
	 */
	public function getNew( string $id, array $args = [] ) : mixed;

	/**
	 * Store a value via key to retrieve later
	 *
	 * @param string                 $id    The name/key of the item
	 * @param callable|object|string $value The value to store
	 * @return callable|object|string
	 */
	public function set( string $id, mixed $value ) : callable|object|string;

	/**
	 * Clone a given value into a second key
	 *
	 * @param string $src  The source
	 * @param string $dest The destination
	 * @return callable|object|string
	 */
	public function duplicate( string $src, string $dest ) : callable|object|string;

	/**
	 * @param string $id The name/key to be retrieved
	 * @return callable|object|string
	 * @throws \Corpus\Di\Exceptions\UndefinedIdentifierException
	 */
	public function raw( string $id ) : callable|object|string;

	/**
	 * Use reflection to execute a classes constructor with auto-populated parameters
	 *
	 * @param string  $className The class to construct
	 * @param mixed[] $initials  An ordered list of arguments to populate initial arguments on constructor
	 */
	public function constructFromReflectiveParams( string $className, array $initials = [] ) : object;

	/**
	 * Use reflection to execute a callable with auto-populated parameters
	 *
	 * @param mixed[] $initials An ordered list of arguments to populate initial arguments on callable
	 * @return mixed the return value of the callable.
	 */
	public function callFromReflectiveParams( callable $callable, array $initials = [] ) : mixed;

}
