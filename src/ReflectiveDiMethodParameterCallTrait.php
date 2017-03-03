<?php

namespace Corpus\Di;

trait ReflectiveDiMethodParameterCallTrait {

	/**
	 * @param DiInterface       $di
	 * @param \ReflectionMethod $ref
	 * @param array             $initials
	 * @return array
	 */
	protected function getReflectiveDiMethodParams( DiInterface $di, \ReflectionMethod $ref, array $initials = [ ] ) {
		/** @var \ReflectionParameter[] $cParams */
		$cParams   = array_slice($ref->getParameters(), count($initials));
		$arguments = $initials;
		foreach( $cParams as $cParam ) {
			$arguments[] = $di->get($cParam->getName());
		}

		return $arguments;
	}

	/**
	 * @param DiInterface $di
	 * @param string      $className
	 * @param array       $initials
	 * @return object
	 */
	protected function constructInstanceFromReflectiveDiMethodParams( DiInterface $di, $className, array $initials = [ ] ) {
		$inst = new \ReflectionClass($className);
		$ref  = $inst->getConstructor();

		if( $ref instanceof \ReflectionMethod ) {
			$args = $this->getReflectiveDiMethodParams($di, $ref, $initials);

			return $inst->newInstanceArgs($args);
		}

		return new $className;
	}

	/**
	 * @param DiInterface $di
	 * @param object      $class
	 * @param string      $method
	 * @param array       $initials
	 * @return mixed
	 */
	protected function callMethodFromReflectiveDiMethodParams( DiInterface $di, $class, $method, array $initials = [ ] ) {
		$ref  = new \ReflectionMethod($class, $method);
		$args = $this->getReflectiveDiMethodParams($di, $ref, $initials);

		return call_user_func_array([ $class, $method ], $args);
	}

}
