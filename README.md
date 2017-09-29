# Corpus Di

[![Latest Stable Version](https://poser.pugx.org/corpus/di/version)](https://packagist.org/packages/corpus/di)
[![License](https://poser.pugx.org/corpus/di/license)](https://packagist.org/packages/corpus/di)
[![Build Status](https://travis-ci.org/CorpusPHP/Di.svg?branch=master)](https://travis-ci.org/CorpusPHP/Di)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/CorpusPHP/Di/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/CorpusPHP/Di)


A Simple PSR-11 Complaint Di Container

## Requirements

- **php**: >=5.4.0
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