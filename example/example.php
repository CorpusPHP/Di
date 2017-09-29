<?php

require 'vendor/autoload.php';

$di = new \Corpus\Di\Di();

// Eager Loading
$di->set('foo', new Foo());

// Lazy Loading
$di->set('bar', function () {
	return new Bar();
});

// Constructor Parameters
$di->set('baz', function ( $qux ) {
	return new Bar($qux);
});

// Value is memoized, new Bar() is only called once at first `get`.
$bar  = $di->get('bar');
$bar2 = $di->get('bar');

// Calling getNew explicitly avoids the memoization. Constructor params passed as array.
$baz  = $di->getNew('baz', [ 'corge' ]);
$baz2 = $di->getNew('baz', [ 'grault' ]);

// getMany lets you retreive multiple memoized values at once.
list($foo, $bar) = $di->getMany([ 'foo', 'bar' ]);

// getManyNew lets you retreive multiple new values at once, providing for arguments.
list($baz, $baz2) = $di->getManyNew([ [ 'baz', [ 'corge' ] ], [ 'baz', [ 'grault' ] ] ]);

// callFromReflectiveParams lets you execute a callable reflectively using the parameter name as the key
$di->callFromReflectiveParams(function ( Foo $foo, Bar $bar ) { /* ... */ });

// constructFromReflectiveParams works like callFromReflectiveParams except on a constructor,
// returning an instance of the requested class
$bazInst = $di->constructFromReflectiveParams("Baz" /* Baz::class works in 5.5+ */);