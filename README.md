# Dot Notation

This supports a dotted syntax similar to [MongoDB's dot notation][1] for creating
and manipulating deeply nested arrays compactly in PHP. This notation is also used by 
other projects, such as Elasticsearch and CakePHP.

## Installation

```
composer require best/dot-notation
```

## Overview

This package has a single class with static methods, `\Best\DotNotation`.

Each method takes an array as its first argument, and then a "key path" as its
second argument. 

The key path can include dots to indicate subkeys of arrays to access or to denote
arrays in a more compact form.

For example, to access the key `'child'` of the array key `'parent'` on the variable 
`$container`, you would do:

```php
$container = array('parent' => array('child' => '2')); 
$childElement = \Best\DotNotation::get($container, 'parent.child');
// $childElemetn === '2' here

```
You can also used numbers as keys. For example, to access the second array element in the key `'parent'` of the variable
`$container`, you would do:

```php
$container = array('parent' => array('child1', 'child2'));
$secondElement = \Best\DotNotation::get($container, 'parent.2');
// $secondElement === 'child2' here.
```

### Escaping dot to include it in keys

If you want to include a literal dot inside a key name, you can escape it with a backslash.

```php
$container = array('my.dotted.key' => 'value');
$value = \Best\DotNotation::get($container, 'my\.dotted\.key'));
// equals: 'value'
```

Backslash is not treated as a special character in any other case, though. So you
can use them in keys as long as it doesn't preceed a dot. Neither backslashes nor dots
in values are transformed in any way, either.
 
## Methods

Most of these methods require a valid key path. A valid key path is
either an integer or non-empty string. Passing an invalid key path will
result in a `\Best\DotNotation\BadKeyPath` exception.

### `get(array $array, string|int $keyPath): mixed`

Get the value stored at the key path.

If any of the keys do not exist in the key path, this will 
throw a `\Best\DotNotation\KeyNotFound` exception.

### `getOrNull(array $array, string|int $keyPath): mixed`

Get the value stored at the key path.

If any of the keys do not exist in the key path, this will
return null.

### `getOrDefault(array $array, string|int $keyPath, mixed $defaultValue): mixed`

Get the value stored at the key path.

If any of the keys do not exist in the key path, this will
return the default value from the third argument.

### `has(array $array, string|int $keyPath): bool`

Whether the array has a value at the key path.

Note that even if this value is `null`, this will still
return `true`.

### `set(): array`

Set the value 

### `setAndOverride(array $array, string|int $keyPath): array`

Set the path, and ignore any invalid intermediate keys, setting them to empty
arrays along the way.

The new array with the key set is returned.
 
### `remove()`

Remove the value from the key path if it exists. A `\Best\DotNotation\KeyNotFound` exception is thrown
if any of the keys in the key path do not exist.

### `removeIfExists(array $array, string|int $keyPath)`

Remove the value from the key path if it exists. No exception is thrown
if the key path does not exist. 

The new array with the key removed is returned.

### `expand(array $dottedArray): array`

Recursively expand the dots inside the keys into new arrays. Returns the newly expanded array.

If any inconsistent keys are detected (some keys are defined as scalars in some keys and are implicitly
arrays in other keys), a `\Best\DotNotation\InconsistentKeyTypes`
exception will be thrown.

### `compact(array $array): array`

Compact the array into a dotted array, with keys, suitable for passing
to `expand()`.

For example:

```php
use Best\DotNotation;

$array = DotNotation::compact(array(
  'my' => array(
      'dotted' ==> array(
          'key' => 'value'
      )
  )
));
// returns the dotted array:
array('my.dotted.key' => 'value');
```

### `compactWithIntegerKeys(array $array): array`

Compact the array as in `compact()`, but also compact integer keys,
so that the array will be as flat as possible.

For example,


[1]: http://docs.mongodb.org/manual/core/document/#dot-notation
[2]: http://framework.zend.com/
