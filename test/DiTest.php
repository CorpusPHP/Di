<?php

namespace Corpus\Test\Di;

use Corpus\Di\Di;
use Corpus\Di\DiInterface;
use PHPUnit\Framework\TestCase;

class demoClass {

	public function __construct(demoValue $test_class) {

	}

}

class demoValue {

	public function __invoke() {
		return true;
	}

}

class demoInvokeWithParam {

	public $test_class;

	public function __invoke($test_class) {
		$this->test_class = $test_class;

		return 21;
	}

}

class DiTest extends TestCase {

	protected function getPopulatedDi() : DiInterface {
		$di = new Di;

		// test callbacks
		$int = 10;
		$di->set('test_callback', function (?int $val = null) use ( &$int ) {
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

	public function testReflectiveInjection() : void {
		$di = $this->getPopulatedDi();

		$di->set('test_reflective_injection', function ( demoValue $test_class, demoClass $demo_injection ) {
			$this->assertInstanceOf(demoValue::class, $test_class);
			$this->assertInstanceOf(demoClass::class, $demo_injection);

			return 7;
		});

		$this->assertSame(7, $di->get('test_reflective_injection'));
	}

	public function testReflectionInjectionOnInvokable() : void {
		$di = $this->getPopulatedDi();

		$this->assertSame(21, $di->get('demoInvokeWithParam'), 'invoking a stored instance should call __invoke');
		$this->assertInstanceOf(demoInvokeWithParam::class, $di->get('demoInvokeWithParamAsString'), 'invoking the ::class string should INSTANTIATE, not __invoke');
	}

	public function testReflectiveInjection_undefinedException() : void {
		$this->expectException(\Corpus\Di\Exceptions\UndefinedIdentifierException::class);
		$di = $this->getPopulatedDi();

		$di->set('test_reflective_injection', function ( demoValue $test_class, demoClass $demo_injection, $noExist ) { return 7; });
		$di->get('test_reflective_injection');
	}

	public function testReflectiveInjectionWithGetNew() : void {
		$di = $this->getPopulatedDi();

		$di->set('test_reflective_injection2', function ( $ok, demoValue $test_class, demoClass $demo_injection ) {
			$this->assertTrue($ok);
			$this->assertInstanceOf(demoValue::class, $test_class);
			$this->assertInstanceOf(demoClass::class, $demo_injection);

			return 7;
		});

		$this->assertSame(7, $di->getNew('test_reflective_injection2', [true]));
	}

	public function testReflectiveInjectionWithGetNewOfDefinedEntries() : void {
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

	public function testHas() : void {
		$di = new Di;

		$this->assertFalse($di->has('test_object'));
		$di->set('test_object', function () {
			return (object)[ 1, 2, 3 ];
		});
		$this->assertTrue($di->has('test_object'));
	}

	public function testInvalidEntries() : void {
		$this->expectException(\Corpus\Di\Exceptions\InvalidArgumentException::class);
		$di = new Di;

		// test scalars
		$di->set('test_scalar', 7);
		$di->set('test_scalar2', "my awesome string");
	}

	public function testGet() : void {
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

	public function testGetMany() : void {
		$di = $this->getPopulatedDi();

		[$one, $two, $three] = $di->getMany([ 'test_callback', 'test_callback', 'test_callback' ]);
		$this->assertSame(11, $one);
		$this->assertSame(11, $two);
		$this->assertSame(11, $three);

		[$obj1, $obj2] = $di->getMany([ 'test_object', 'test_object' ]);
		$this->assertSame($obj1, $obj2);
	}

	public function testGetNew() : void {
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

	public function testGetManyNew() : void {
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

	public function testNamedDiInitialValues() : void {
		$di = new Di;
		$di->set('foo', function ( $a, $b, $c = 0, $d = 0 ) : int { return $d + (10 * $c) + (100 * $b) + (1000 * $a); });
		$di->set('a', function () : int { return 2; });
		$di->set('b', function () : int { return 7; });

		$this->assertSame(2700, $di->getNew('foo'));
		$this->assertSame(1000, $di->getNew('foo', [ 1, 0 ]));
		$this->assertSame(1234, $di->getNew('foo', [ 1, 2, 3, 4 ]));
		$this->assertSame(2309, $di->getNew('foo', [ 1 => 3, 3 => 9 ]));
		$this->assertSame(2300, $di->getNew('foo', [ 'b' => 3 ]));
		$this->assertSame(2309, $di->getNew('foo', [ 'b' => 3, 'd' => 9 ]));
		$this->assertSame(2709, $di->getNew('foo', [ 'd' => 9 ]), 'tests that an optional *after* another option is populated.');
		$this->assertSame(2900, $di->getNew('foo', [ 1 => 9, 'b' => 3 ]), 'tests that indexed initials override named');
	}

	public function testDuplicate() : void {
		$di = $this->getPopulatedDi();

		$di->duplicate('test_object', 'duplicate_object');
		$this->assertSame( $di->raw('test_object'), $di->raw('duplicate_object') );

		$obj1 = $di->get('test_object');
		$obj2 = $di->get('duplicate_object');
		$this->assertNotSame($obj1, $obj2);
	}

	public function testRaw() : void {
		$di = new Di;

		$lambda  = function () { return 0; };
		$lambda2 = function () { return 0; };

		$di->set('callback', $lambda);
		$raw = $di->raw('callback');
		$this->assertEquals($raw, $lambda);
		$this->assertNotSame($raw, $lambda2);
		$this->assertNotEquals($raw, 0);
	}

	public function testGetManyNewInvalidArgumentException_tooMany() : void {
		$this->expectException(\Corpus\Di\Exceptions\InvalidArgumentException::class);
		$di = $this->getPopulatedDi();

		$di->getManyNew([ [ 'test_argument_callback', 1, 2 ] ]);
	}

	public function testGetManyNewInvalidArgumentException_tooFew() : void {
		$this->expectException(\Corpus\Di\Exceptions\InvalidArgumentException::class);
		$di = $this->getPopulatedDi();

		$di->getManyNew([ [ ] ]);
	}

	public function testGetManyNewInvalidArgumentException_badKeyType() : void {
		$this->expectException(\Corpus\Di\Exceptions\InvalidArgumentException::class);
		$di = $this->getPopulatedDi();

		$di->getManyNew([ 1 ]);
	}

	public function testGetUndefinedException() : void {
		$this->expectException(\Corpus\Di\Exceptions\UndefinedIdentifierException::class);
		$di = new Di;

		// SHOULD throw an exception
		$di->get('undefined_key');
	}

	public function testGetNewUndefinedException() : void {
		$this->expectException(\Corpus\Di\Exceptions\UndefinedIdentifierException::class);
		$di = new Di;

		// SHOULD throw an exception
		$di->getNew('undefined_key');
	}

	public function testRawUndefinedException() : void {
		$this->expectException(\Corpus\Di\Exceptions\UndefinedIdentifierException::class);
		$di = new Di;

		// SHOULD throw an exception
		$di->raw('undefined_key');
	}

	public function testCallFromReflectiveParams_callableArray() : void {
		$di = $this->getPopulatedDi();

		$ok = new class($this) {

			public $that;
			public function __construct(DiTest $that) {
				$this->that = $that;
			}

			public $success = false;
			public function soup($test_class) {
				$this->success = true;
				$this->that->assertInstanceOf(demoValue::class, $test_class);

				return "foo-bar";
			}

};

		$this->assertSame("foo-bar", $di->callFromReflectiveParams([$ok, 'soup']));
		$this->assertTrue($ok->success);
	}

	public function testConstructFromReflectiveParams_invalidClassNameException() : void {
		$this->expectExceptionCode(3);
		$this->expectException(\Corpus\Di\Exceptions\InvalidArgumentException::class);
		$di = $this->getPopulatedDi();

		try {
			$di->constructFromReflectiveParams('classDoesNotExist');
		}catch(\Exception $ex) {
			$this->assertInstanceOf(\ReflectionException::class, $ex->getPrevious());

			throw $ex;
		}
	}

}
