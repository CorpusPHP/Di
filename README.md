# Corpus Di

[![Latest Stable Version](https://poser.pugx.org/corpus/di/version)](https://packagist.org/packages/corpus/di)
[![License](https://poser.pugx.org/corpus/di/license)](https://packagist.org/packages/corpus/di)
[![CI](https://github.com/CorpusPHP/Di/workflows/CI/badge.svg?)](https://github.com/CorpusPHP/Di/actions?query=workflow%3ACI)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/CorpusPHP/Di/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/CorpusPHP/Di)


A Simple PSR-11 Complaint Di Container

## Requirements

- **php**: >=7.2
- **psr/container**: ~1.0 || ~2.0

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

```

## Documentation

### Class: \Corpus\Di\Di

#### Method: Di->getMany

```php
function getMany(array $ids) : array
```

Retrieve multiple item; cached if existing. For use with list()

##### Parameters:

- ***string[]*** `$ids` - The names/keys of the items

##### Returns:

- ***array***

---

#### Undocumented Method: `Di->get($id)`

---

#### Method: Di->getManyNew

```php
function getManyNew(array $data) : array
```

Retrieve multiple item. For use with list()

##### Parameters:

- ***array[]*** `$data` - The array of (names/keys / argument) pair tuple of the items

##### Returns:

- ***array***

---

#### Method: Di->getNew

```php
function getNew($id [, array $args = []])
```

Retrieve an item

##### Parameters:

- ***string*** `$id` - The name/key of the item
- ***array*** `$args`

---

#### Method: Di->duplicate

```php
function duplicate(string $src, string $dest)
```

Clone a given value into a second key

##### Parameters:

- ***string*** `$src` - The source
- ***string*** `$dest` - The destination

---

#### Method: Di->set

```php
function set(string $id, $value)
```

Store a value via key to retrieve later

##### Parameters:

- ***string*** `$id` - The name/key of the item
- ***mixed*** `$value` - The value to store

---

#### Undocumented Method: `Di->has($id)`

---

#### Method: Di->raw

```php
function raw(string $id)
```

##### Parameters:

- ***string*** `$id` - The name/key to be retrieved

---

#### Method: Di->constructFromReflectiveParams

```php
function constructFromReflectiveParams(string $className [, array $initials = []]) : object
```

Use reflection to execute a classes constructor with auto-populated parameters

##### Parameters:

- ***string*** `$className` - The class to construct
- ***array*** `$initials` - An ordered list of arguments to populate initial arguments on constructor

---

#### Method: Di->callFromReflectiveParams

```php
function callFromReflectiveParams(callable $callable [, array $initials = []])
```

Use reflection to execute a callable with auto-populated parameters

##### Parameters:

- ***array*** `$initials` - An ordered list of arguments to populate initial arguments on callable

##### Returns:

- ***mixed*** - the return value of the callable.

### Class: \Corpus\Di\Exceptions\UndefinedIdentifierException

Thrown when attempting to retrieve a key that does not exist.