# Dot Notation

This supports a syntax similar to MongoDB's dot notation http://docs.mongodb.org/manual/core/document/#dot-notation
for creating deeply nested arrays compactly in PHP.

## Installation

Do:
```sh
php composer.phar require dmeybohm/dot-notation
```

Then:

```sh
php composer.phar install
```

## Usage

If you have your autoloader configured, you can just use the class. The
interface consists of two static methods: expand() and fromFile(). Both methods
transform the dotted notation to the equivalent expanded arrays: 

```php
<?php
use Dmeybohm\DotNotation;
$array = DotNotation::expand(array('my.dotted.key' => 'value'));
// returns an array that looks like:
array(
     'my' => array(
         'dotted' => array(
             'key' => 'value'
         )
     )
);

// Load from a file. The include path is searched:
$array = DotNotation::fromFile('myfile.php')
```

In myfile.php:
```php
<?php
return array('my.dotted.key' => 'value')
```

Note that the fromFile method requires that the included file return the
array that is to be expanded.

Dotted keys can be specified many times in the same array, and all the values will be 
appended to the arrays. 
```php
$array = DotNotation::expand(array(
    'my.dotted.key' => 'value1',
    'my.dotted.other.key' => 'value2',
));
// expands to:
array(
    'my' => array(
        'dotted' => array(
            'key'   => 'value1',
            'other' => array(
                'key' => 'value2'
            )
        )
    )
);

```
Keys have to consistenly be used as arrays. If you change a value to an array, it
will be overridden by the last value used:

```php
$array = DotNotation::expand(array(
    'my.dotted.key' => 'value1',
    'my.dotted.key.subkey' => 'value2'
));
// expands to:
array(
    'my' => array(
        'dotted' => array(
            'key'   =>  array(
                'subkey' => 'value2'
            )
        )
    )
);

```
Here's another example from inside Zend Framework 2 configuration files
where the arrays are deeply nested. Note how much more readable the
dot notation version is in addition to being smaller:
```php
$array = DotNotation::expand(array(
    'view_manager' => array(
        'template_path_stack' => array(
            'album' => __DIR__ . '/../view',
        ),
    ),

    'controllers.invokables' => array(
        'Album\Controller\Album' => 'Album\Controller\AlbumController',
    ),

    'router.routes.album' => array(
        'type'                => 'segment',
        'options.route'       => '/album[/:action][/:id]',
        'options.constraints' => array(
            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'id'     => '[0-9]+',
        ),
        'options.defaults' => array(
            'controller' => 'Album\Controller\Album',
            'action'     => 'index',
        ),
    ),

    'view_manager.template_path_stack.album' => __DIR__ . '/../view',
));

// Produces:
array(
     'controllers' => array(
         'invokables' => array(
             'Album\Controller\Album' => 'Album\Controller\AlbumController',
         ),
     ),
 
     'router' => array(
         'routes' => array(
             'album' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/album[/:action][/:id]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'id'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         'controller' => 'Album\Controller\Album',
                         'action'     => 'index',
                     ),
                 ),
             ),
         ),
     ),
);
```

## Escaping

If you want to include a dot inside a key name, you can escape it with a backslash.
```php
use Dmeybohm\DotNotation;
$array = DotNotation::expand(array('my\.dotted\.key' => 'value'));
// expands to: 
array('my.dotted.key' => 'value')
```
Backslash is not treated as a special character in any other case, though. So you
can use them in keys in places other than before a dot with impunity.

