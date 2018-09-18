# Corpus Di

[![Latest Stable Version](https://poser.pugx.org/corpus/di/version)](https://packagist.org/packages/corpus/di)
[![License](https://poser.pugx.org/corpus/di/license)](https://packagist.org/packages/corpus/di)
[![Build Status](https://travis-ci.org/CorpusPHP/Di.svg?branch=master)](https://travis-ci.org/CorpusPHP/Di)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/CorpusPHP/Di/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/CorpusPHP/Di)


A Simple PSR-11 Complaint Di Container

## Requirements

- **php**: >=7.1.0
- **psr/container**: ~1.0.0

## Installing

Install the latest version with:

```bash
composer require 'corpus/di'
```

## Usage

Getting started with Di the three most important methods follow.
- The `set` method is used to set either the item to return or a lambda to lazily construct it, optionally taking constructor arguments.
- The `get` method is used to retrieve values with memoization after the initial lazy loading.
- The `getNew` is used to invoke the lazy loading creation lambda every call, optionally taking an array of constructor arguments as a second parameter.

```php
<?php

require 'vendor/autoload.php';

$di = new \Corpus\Di\Di();

// Eager Loading
$di->set('foo', new Foo());

$di->get('foo'); // the Foo instance from above

// --- --- --- --- --- ---

// Lazy Loading
$di->set('bar', function () {
	return new Bar();
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
list($foo, $bar) = $di->getMany([ 'foo', 'bar' ]);

// getManyNew lets you retrieve multiple new values at once, providing for arguments.
list($baz, $baz2) = $di->getManyNew([ [ 'baz', [ 'corge' ] ], [ 'baz', [ 'grault' ] ] ]);

$di->callFromReflectiveParams(function(Bar $bar, Baz $baz){
	// Callable called with parameters automatically populated based on their name
	// $bar => 'bar'
});

// Construct a class auto-populating constructor parameters based on their name
$controller1 = $di->constructFromReflectiveParams(MyController::class);
$controller2 = $di->constructFromReflectiveParams('MyController');

```

## Documentation

### Class: \Corpus\Di\Di

#### Method: Di->getMany

```php
function getMany($ids)
```

Retrieve multiple item; cached if existing. For use with list()

---

#### Method: Di->get

```php
function get($id)
```

---

#### Method: Di->getManyNew

```php
function getManyNew($data)
```

Retrieve multiple item. For use with list()

---

#### Method: Di->getNew

```php
function getNew($id [, $args = array()])
```

Retrieve an item

---

#### Method: Di->duplicate

```php
function duplicate($src, $dest)
```

Clone a given value into a second key

---

#### Method: Di->set

```php
function set($id, $value)
```

Store a value via key to retrieve later

---

#### Method: Di->has

```php
function has($id)
```

---

#### Method: Di->raw

```php
function raw($id)
```

---

#### Method: Di->constructFromReflectiveParams

```php
function constructFromReflectiveParams($className [, $initials = array()])
```

Use reflection to execute a classes constructor with auto-populated parameters

---

#### Method: Di->callFromReflectiveParams

```php
function callFromReflectiveParams($callable [, $initials = array()])
```

Use reflection to execute a callable with auto-populated parameters

### Class: \Corpus\Di\Exceptions\UndefinedIdentifierException

Class UndefinedIdentifierException

Thrown when attempting to retrieve a key that does not exist.