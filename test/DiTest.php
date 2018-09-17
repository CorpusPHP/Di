<?php

namespace Corpus\Test\Di;

use Corpus\Di\Di;
use Corpus\Di\DiInterface;
use PHPUnit\Framework\TestCase;

class demoClass {
	public function __construct(demoValue $test_class){

	}
}

class demoValue {
	public function __invoke(){
		return true;
	}
}

class demoInvokeWithParam {
	public $test_class;

	public function __invoke($test_class){
		$this->test_class = $test_class;

		return 21;
	}
}

class DiTest extends TestCase {

	protected function getPopulatedDi() : DiInterface {
		$di = new Di();

		// test callbacks
		$int = 10;
		$di->set('test_callback', function (int $val = null) use ( &$int ) {
			if($val !== null) {
				$int = $val;
			}else {
				$int += 1;
			}

			return $int;
		});

		$di->set('test_argument_callback', function ( $number, $number2 ) {
			return $number + $number2;
		});

		$di->set('test_object', function () {
			return (object)[ 1, 2, 3 ];
		});

		$di->set('test_class', demoValue::class);
		$di->set('demo_injection', demoClass::class);

		$di->set('demoInvokeWithParam', new demoInvokeWithParam);
		$di->set('demoInvokeWithParamAsString', demoInvokeWithParam::class);

		$inst = new demoValue;
		$di->set('test_class_inst', $inst);

		return $di;
	}

	public function testReflectiveInjection() {
		$di = $this->getPopulatedDi();

		$di->set('test_reflective_injection', function ( demoValue $test_class, demoClass $demo_injection ) {
			$this->assertInstanceOf(demoValue::class, $test_class);
			$this->assertInstanceOf(demoClass::class, $demo_injection);

			return 7;
		});

		$this->assertSame(7, $di->get('test_reflective_injection'));
	}

	public function testReflectionInjectionOnInvokable() {
		$di = $this->getPopulatedDi();

		$this->assertSame(21, $di->get('demoInvokeWithParam'), 'invoking a stored instance should call __invoke');
		$this->assertInstanceOf(demoInvokeWithParam::class, $di->get('demoInvokeWithParamAsString'), 'invoking the ::class string should INSTANTIATE, not __invoke');
	}

	/**
	 * @expectedException \Corpus\Di\Exceptions\UndefinedIdentifierException
	 */
	public function testReflectiveInjection_undefinedException() {
		$di = $this->getPopulatedDi();

		$di->set('test_reflective_injection', function ( demoValue $test_class, demoClass $demo_injection, $noExist ) { return 7; });
		$di->get('test_reflective_injection');
	}

	public function testReflectiveInjectionWithGetNew(){
		$di = $this->getPopulatedDi();

		$di->set('test_reflective_injection2', function ( $ok, demoValue $test_class, demoClass $demo_injection ) {
			$this->assertTrue($ok);
			$this->assertInstanceOf(demoValue::class, $test_class);
			$this->assertInstanceOf(demoClass::class, $demo_injection);

			return 7;
		});

		$this->assertSame(7, $di->getNew('test_reflective_injection2', [true]));
	}

	public function testReflectiveInjectionWithGetNewOfDefinedEntries(){
		$di = $this->getPopulatedDi();

		$di->set('test_reflective_injection', function ( demoValue $test_class, demoClass $demo_injection ) {
			$this->assertInstanceOf(demoValue::class, $test_class);
			$this->assertInstanceOf(demoClass::class, $demo_injection);

			return [$test_class, $demo_injection];
		});

		$test = new demoValue;
		$demo = new demoClass($test);

		$this->assertSame([$test, $demo], $di->getNew('test_reflective_injection', [$test, $demo]));
	}

	public function testHas(){
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
	public function testInvalidEntries(){
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

		$this->assertInstanceOf(demoValue::class, $di->get('test_class'));
		$this->assertInstanceOf(demoClass::class, $di->get('demo_injection'));

		$this->assertTrue($di->get('test_class_inst'));

		$ten = $di->getNew('test_argument_callback', [ 4, 6 ]);
		$this->assertSame(4 + 6, $ten);

	}

	public function testGetMany() {
		$di = $this->getPopulatedDi();

		[$one, $two, $three] = $di->getMany([ 'test_callback', 'test_callback', 'test_callback' ]);
		$this->assertSame(11, $one);
		$this->assertSame(11, $two);
		$this->assertSame(11, $three);

		[$obj1, $obj2] = $di->getMany([ 'test_object', 'test_object' ]);
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

		[$one, $two, $three] = $di->getManyNew([ 'test_callback', 'test_callback', 'test_callback' ]);
		$this->assertSame(11, $one);
		$this->assertSame(12, $two);
		$this->assertSame(13, $three);

		[ $four, $five, $six ] = $di->getManyNew([ [ 'test_callback' ], [ 'test_callback', [] ], [ 'test_callback', [ 1024 ] ] ]);
		$this->assertSame(14, $four);
		$this->assertSame(15, $five);
		$this->assertSame(1024, $six, 'value should match that set in the call arguments');

		[$obj1, $obj2] = $di->getManyNew([ 'test_object', 'test_object' ]);
		$this->assertNotSame($obj1, $obj2);
	}

	public function testDuplicate() {
		$di = $this->getPopulatedDi();

		$di->duplicate('test_object', 'duplicate_object');
		$this->assertSame( $di->raw('test_object'), $di->raw('duplicate_object') );

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

		$di->getManyNew([ [ ] ]);
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

	public function testCallFromReflectiveParams_callableArray(){
		$di = $this->getPopulatedDi();

		$ok = new class($this) {
			public $that;
			public function __construct(DiTest $that){
				$this->that = $that;
			}
			public $success = false;
			public function soup($test_class){
				$this->success = true;
				$this->that->assertInstanceOf(demoValue::class, $test_class);

				return "foo-bar";
			}
		};

		$this->assertSame("foo-bar", $di->callFromReflectiveParams([$ok, 'soup']));
		$this->assertTrue($ok->success);
	}

	/**
	 * @expectedException \Corpus\Di\Exceptions\InvalidArgumentException
	 * @expectedExceptionCode 3
	 */
	public function testConstructFromReflectiveParams_invalidClassNameException(){
		$di = $this->getPopulatedDi();

		try {
			$di->constructFromReflectiveParams('classDoesNotExist');
		}catch(\Exception $ex) {
			$this->assertInstanceOf(\ReflectionException::class, $ex->getPrevious());

			throw $ex;
		}
	}

}
