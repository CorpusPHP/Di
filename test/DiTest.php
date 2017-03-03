<?php

namespace Corpus\Test\Di;

use Corpus\Di\Di;

class demoClass {

	public function __construct( demoValue $test_class ) {
	}
}

class demoValue {

	public function __invoke() {
		return true;
	}
}

class DiTest extends \PHPUnit_Framework_TestCase {

	protected function getPopulatedDi() {
		$di = new Di();

		// test callbacks
		$int = 10;
		$di->set('test_callback', function () use ( &$int ) {
			$int += 1;

			return $int;
		});

		$di->set('test_argument_callback', function ( $number, $number2 ) {
			return $number + $number2;
		});

		$di->set('test_object', function () {
			return (object)[ 1, 2, 3 ];
		});

		$di->set('test_class', '\\Corpus\\Test\\Di\\demoValue');

		$di->set('demo_injection', '\\Corpus\\Test\\Di\\demoClass');

		$inst = new demoValue;
		$di->set('test_class_inst', $inst);

		return $di;
	}

	public function testHas() {
		$di = new Di();

		$this->assertFalse($di->has('test_object'));
		$di->set('test_object', function () {
			return (object)[ 1, 2, 3 ];
		});
		$this->assertTrue($di->has('test_object'));
	}

	/**
	 * @expectedException \Corpus\Di\Exceptions\InvalidArgumentException
	 */
	public function testInvalidEntries() {
		$di = new Di();

		// test scalars
		$di->set('test_scalar', 7);
		$di->set('test_scalar2', "my awesome string");
	}

	public function testGet() {
		$di = $this->getPopulatedDi();

		$this->assertSame(11, $di->get('test_callback'));
		// call the same request a second time to test memoization.
		$this->assertSame(11, $di->get('test_callback'), "Memoization failed");

		$this->assertSame($di->get('test_object'), $di->get('test_object'));

		$this->assertInstanceOf('\\Corpus\\Test\\Di\\demoValue', $di->get('test_class'));
		$this->assertInstanceOf('\\Corpus\\Test\\Di\\demoClass', $di->get('demo_injection'));

		$this->assertTrue($di->get('test_class_inst'));

		$ten = $di->getNew('test_argument_callback', [ 4, 6 ]);
		$this->assertSame(4 + 6, $ten);
	}

	public function testGetMany() {
		$di = $this->getPopulatedDi();

		list($one, $two, $three) = $di->getMany([ 'test_callback', 'test_callback', 'test_callback' ]);
		$this->assertSame(11, $one);
		$this->assertSame(11, $two);
		$this->assertSame(11, $three);

		list($obj1, $obj2) = $di->getMany([ 'test_object', 'test_object' ]);
		$this->assertSame($obj1, $obj2);
	}

	public function testGetNew() {
		$di = $this->getPopulatedDi();

		$this->assertSame(11, $di->getNew('test_callback'));
		// call the same request a second time to test memoization.
		$this->assertSame(12, $di->getNew('test_callback'), "Memoizing, should not!");

		$eight = $di->getNew('test_argument_callback', [ 3, 5 ]);
		$this->assertSame(3 + 5, $eight);

		$this->assertNotSame($di->getNew('test_object'), $di->getNew('test_object'));
		// make sure we're getting a new instance
		$this->assertNotSame($di->get('test_object'), $di->getNew('test_object'));
	}

	public function testGetManyNew() {
		$di = $this->getPopulatedDi();

		list($one, $two, $three) = $di->getManyNew([ 'test_callback', 'test_callback', 'test_callback' ]);
		$this->assertSame(11, $one);
		$this->assertSame(12, $two);
		$this->assertSame(13, $three);

		list($obj1, $obj2) = $di->getManyNew([ 'test_object', 'test_object' ]);
		$this->assertNotSame($obj1, $obj2);
	}

	public function testDuplicate() {
		$di = $this->getPopulatedDi();

		$di->duplicate('test_object', 'duplicate_object');
		$this->assertSame($di->raw('test_object'), $di->raw('duplicate_object'));

		$obj1 = $di->get('test_object');
		$obj2 = $di->get('duplicate_object');
		$this->assertNotSame($obj1, $obj2);
	}

	public function testRaw() {
		$di = new Di();

		$lambda  = function () { return 0; };
		$lambda2 = function () { return 0; };

		$di->set('callback', $lambda);
		$raw = $di->raw('callback');
		$this->assertEquals($raw, $lambda);
		$this->assertNotSame($raw, $lambda2);
		$this->assertNotEquals($raw, 0);
	}

	/**
	 * @expectedException \Corpus\Di\Exceptions\InvalidArgumentException
	 */
	public function testGetManyNewInvalidArgumentException_tooMany() {
		$di = $this->getPopulatedDi();

		$di->getManyNew([ [ 'test_argument_callback', 1, 2 ] ]);
	}

	/**
	 * @expectedException \Corpus\Di\Exceptions\InvalidArgumentException
	 */
	public function testGetManyNewInvalidArgumentException_tooFew() {
		$di = $this->getPopulatedDi();

		$di->getManyNew([ [] ]);
	}

	/**
	 * @expectedException \Corpus\Di\Exceptions\InvalidArgumentException
	 */
	public function testGetManyNewInvalidArgumentException_badKeyType() {
		$di = $this->getPopulatedDi();

		$di->getManyNew([ 1 ]);
	}

	/**
	 * @expectedException \Corpus\Di\Exceptions\UndefinedIdentifierException
	 */
	public function testGetUndefinedException() {
		$di = new Di();

		//SHOULD throw an exception
		$di->get('undefined_key');
	}

	/**
	 * @expectedException \Corpus\Di\Exceptions\UndefinedIdentifierException
	 */
	public function testGetNewUndefinedException() {
		$di = new Di();

		//SHOULD throw an exception
		$di->getNew('undefined_key');
	}

	/**
	 * @expectedException \Corpus\Di\Exceptions\UndefinedIdentifierException
	 */
	public function testRawUndefinedException() {
		$di = new Di();

		//SHOULD throw an exception
		$di->raw('undefined_key');
	}


}
