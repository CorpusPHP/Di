<?php

require 'vendor/autoload.php';

$di = new \Corpus\Di\Di;

// Eager Loading
$di->set('foo', new Foo);

$di->get('foo'); // the Foo instance from above

// --- --- --- --- --- ---

// Lazy Loading
$di->set('bar', function () {
	return new Bar;
});

// Value is memoized, new Bar() is only called once at first `get`.
$bar1 = $di->get('bar');
$bar2 = $di->get('bar');

// --- --- --- --- --- ---

// Constructor Parameters
$di->set('baz', function ( $qux ) {
	return new Baz($qux);
});

// Calling getNew explicitly avoids the memoization. Constructor params passed as array.
$baz  = $di->getNew('baz', [ 'corge' ]);
$baz2 = $di->getNew('baz', [ 'grault' ]);

// --- --- --- --- --- ---

// Auto-Constructor Parametrization
$di->set('qux', Qux::class);

$qux1 = $di->get('qux'); // New instance of Qux
$qux2 = $di->get('qux'); // Memoized instance of Qux

// --- --- --- --- --- ---

// Lazy Loading with auto-arguments.
$di->set('quux', function ( Qux $qux ) {
	return new Quux($qux);
});

$quux = $di->get('quux'); // Instance of Quux given the previous instance of Qux automatically

// --- --- --- --- --- ---

// getMany lets you retrieve multiple memoized values at once.
[$foo, $bar] = $di->getMany([ 'foo', 'bar' ]);

// getManyNew lets you retrieve multiple new values at once, providing for arguments.
[$baz, $baz2] = $di->getManyNew([ [ 'baz', [ 'corge' ] ], [ 'baz', [ 'grault' ] ] ]);

$di->callFromReflectiveParams(function(Bar $bar, Baz $baz){
	// Callable called with parameters automatically populated based on their name
	// $bar => 'bar'
});

// Construct a class auto-populating constructor parameters based on their name
$controller1 = $di->constructFromReflectiveParams(MyController::class);
$controller2 = $di->constructFromReflectiveParams('MyController');
