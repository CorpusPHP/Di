# Corpus Di

[![Latest Stable Version](https://poser.pugx.org/corpus/di/v/stable.png)](https://packagist.org/packages/corpus/di)
[![License](https://poser.pugx.org/corpus/di/license.png)](https://packagist.org/packages/corpus/di)
[![Build Status](https://travis-ci.org/CorpusPHP/Di.svg?branch=master)](https://travis-ci.org/CorpusPHP/Di)

A Simple Di Container

## Installing

Corpus Di is available through Packagist via Composer.

```json
{
	"require": {
		"corpus/di": "dev-master",
	}
}
				
```

## Documentation

### Class: Di \[ `\Corpus\Di` \]

#### Method: `Di`->`getMany($names)`

Retrieve multiple item; cached if existing. For use with list()  
  


##### Parameters:

- ***string[]*** `$names` - The names/keys of the items


##### Returns:

- ***mixed[]***


---

#### Method: `Di`->`get($name)`

Retrieve an item; cached if existing  
  


##### Parameters:

- ***string*** `$name` - The name/key of the item


##### Returns:

- ***mixed***


---

#### Method: `Di`->`getManyNew($data)`

Retrieve multiple item. For use with list()  
  


##### Parameters:

- ***array[]*** `$data` - The array of (names/keys / argument) pair tuple of the items


##### Returns:

- ***mixed[]***


---

#### Method: `Di`->`getNew($name [, $args = array()])`

Retrieve an item  
  


##### Parameters:

- ***string*** `$name` - The name/key of the item
- ***mixed[]*** `$args`


##### Returns:

- ***mixed***


---

#### Method: `Di`->`duplicate($src, $dest)`

Clone a given value into a second key  
  


##### Parameters:

- ***string*** `$src` - The source
- ***string*** `$dest` - The destination


##### Returns:

- ***mixed***


---

#### Method: `Di`->`set($name, $value)`

Store a value via key to retrieve later  
  


##### Parameters:

- ***string*** `$name` - The name/key of the item
- ***mixed*** `$value` - The value to store


##### Returns:

- ***mixed***


---

#### Method: `Di`->`raw($name)`

##### Parameters:

- ***string*** `$name` - The name/key to be retrieved


##### Returns:

- ***mixed***

### Class: DiInterface \[ `\Corpus\Di` \]

Corpus Di Dependency Injection Container

#### Method: `DiInterface`->`getManyNew($data)`

Retrieve multiple item. For use with list()  
  


##### Parameters:

- ***array[]*** `$data` - The array of (names/keys / argument) pair tuple of the items


##### Returns:

- ***mixed[]***


---

#### Method: `DiInterface`->`getMany($names)`

Retrieve multiple item; cached if existing. For use with list()  
  


##### Parameters:

- ***string[]*** `$names` - The names/keys of the items


##### Returns:

- ***mixed[]***


---

#### Method: `DiInterface`->`getNew($name [, $args = array()])`

Retrieve an item  
  


##### Parameters:

- ***string*** `$name` - The name/key of the item
- ***mixed[]*** `$args`


##### Returns:

- ***mixed***


---

#### Method: `DiInterface`->`set($name, $value)`

Store a value via key to retrieve later  
  


##### Parameters:

- ***string*** `$name` - The name/key of the item
- ***mixed*** `$value` - The value to store


##### Returns:

- ***mixed***


---

#### Method: `DiInterface`->`get($name)`

Retrieve an item; cached if existing  
  


##### Parameters:

- ***string*** `$name` - The name/key of the item


##### Returns:

- ***mixed***


---

#### Method: `DiInterface`->`duplicate($src, $dest)`

Clone a given value into a second key  
  


##### Parameters:

- ***string*** `$src` - The source
- ***string*** `$dest` - The destination


##### Returns:

- ***mixed***


---

#### Method: `DiInterface`->`raw($name)`

##### Parameters:

- ***string*** `$name` - The name/key to be retrieved


##### Returns:

- ***mixed***

### Class: UndefinedIdentifierException \[ `\Corpus\Di\Exceptions` \]

Class UndefinedIdentifierException

