# Corpus Di

[![Latest Stable Version](https://poser.pugx.org/corpus/di/v/stable.png)](https://packagist.org/packages/corpus/di)
[![License](https://poser.pugx.org/corpus/di/license.png)](https://packagist.org/packages/corpus/di)
[![Build Status](https://travis-ci.org/CorpusPHP/Di.svg?branch=master)](https://travis-ci.org/CorpusPHP/Di)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/CorpusPHP/Di/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/CorpusPHP/Di/?branch=master)

A Simple Di Container

## Requirements

- PHP 5.3.0+

## Installing

Corpus Di is available through Packagist via Composer.

```json
{
	"require": {
		"corpus/di": "1.*"
	}
}
```

## Usage

Getting started with Di the three most important methods follow.
- The `set` method is used to set either the item to return or a lambda to lazily construct it, optionally taking constructor arguments.
- The `get` method is used to retrieve values with memoization after the initial lazy loading.
- The `getNew` is used to invoke the lazy loading creation lambda every call, optionally taking an array of constructor arguments as a second parameter.

```php
<?php

require('vendor/autoload.php');

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
list($baz, $baz2) = $di->getManyNew([ ['baz', ['corge']], ['baz', ['grault']] ]);


```

Note: [5.4 array syntax](http://us3.php.net/manual/en/language.types.array.php) was used for terseness in this example but 5.3 is completely supported.

## Documentation

### Class: Di \[ `\Corpus\Di` \]

#### Method: `Di->getMany($names)`

Retrieve multiple item; cached if existing. For use with list()  
  


##### Parameters:

- ***string[]*** `$names` - The names/keys of the items


##### Returns:

- ***mixed[]***


---

#### Method: `Di->get($name)`

Retrieve an item; cached if existing  
  


##### Parameters:

- ***string*** `$name` - The name/key of the item


##### Returns:

- ***mixed***


---

#### Method: `Di->getManyNew($data)`

Retrieve multiple item. For use with list()  
  


##### Parameters:

- ***array[]*** `$data` - The array of (names/keys / argument) pair tuple of the items


##### Returns:

- ***mixed[]***


---

#### Method: `Di->getNew($name [, $args = array()])`

Retrieve an item  
  


##### Parameters:

- ***string*** `$name` - The name/key of the item
- ***mixed[]*** `$args`


##### Returns:

- ***mixed***


---

#### Method: `Di->duplicate($src, $dest)`

Clone a given value into a second key  
  


##### Parameters:

- ***string*** `$src` - The source
- ***string*** `$dest` - The destination


##### Returns:

- ***mixed***


---

#### Method: `Di->set($name, $value)`

Store a value via key to retrieve later  
  


##### Parameters:

- ***string*** `$name` - The name/key of the item
- ***mixed*** `$value` - The value to store


##### Returns:

- ***mixed***


### Class: UndefinedIdentifierException \[ `\Corpus\Di\Exceptions` \]

Thrown when attempting to retrieve a key that does not exist.

