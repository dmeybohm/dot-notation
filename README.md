# Dot Notation

This supports a syntax similar to [MongoDB's dot notation][1] for creating
deeply nested arrays compactly in PHP.

## Installation

Do:

```bash
php composer.phar require dmeybohm/dot-notation:dev-master
```

## Usage

If you have your autoloader configured, you can just use the class. The
interface consists of two static methods: `expand()` and `fromFile()`. Both methods
transform the dotted notation to the equivalent expanded arrays: 

### `DotNotation::expand()`

Use this to expand an array.

```php
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
```

### `DotNotation::fromFile()`

Use this to expand a file that returns an array. The include path
is searched for the file.

```php
// Load from a file. The include path is searched:
$array = DotNotation::fromFile('myfile.php')
```

In `myfile.php`:

```php
<?php
return array('my.dotted.key' => 'value')
```

### Keys can be appended to and overridden

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

### Escaping dot to include it in keys

If you want to include a dot inside a key name, you can escape it with a backslash.

```php
use Dmeybohm\DotNotation;
$array = DotNotation::expand(array('my\.dotted\.key' => 'value'));
// expands to: 
array('my.dotted.key' => 'value')
```

Backslash is not treated as a special character in any other case, though. So you
can use them in keys as long as it doesn't preceed a dot. Neither backslashes nor dots
in values are transformed in any way, either.

## Zend Framework 2 Configuration Example

Here's another example from inside the example [Zend Framework 2][2] configuration
files where the arrays are deeply nested. Note how much more readable the dot
notation version is in addition to being smaller:

```php
$array = DotNotation::expand(array(
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
    'view_manager' => array(
        'template_path_stack' => array(
            'album' => __DIR__ . '/../view',
        ),
    ),
);
```

Appending keys all together makes it possible to have groups of related
configuration that can be grouped together into sections. Otherwise, you'd have
to have one place for all your controllers configuration, another for all your
routes, and so on:

```php
$array = DotNotation::expand(array(
    //
    // Configuration for the album controller:
    //
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

    //
    // Configuration for the book controller:
    //
    'controllers.invokables' => array(
        'Book\Controller\Book' => 'Book\Controller\BookController',
    ),

    'router.routes.book' => array(
        'type'                => 'segment',
        'options.route'       => '/book[/:action][/:id]',
        'options.constraints' => array(
            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'id'     => '[0-9]+',
        ),
        'options.defaults' => array(
            'controller' => 'Book\Controller\Book',
            'action'     => 'index',
        ),
    ),

    'view_manager.template_path_stack.book' => __DIR__ . '/../view',
));
```

It can also be done the more compact way:

```php
$array = DotNotation::expand(array(
    'controllers.invokables' => array(
        'Album\Controller\Album' => 'Album\Controller\AlbumController',
        'Book\Controller\Book'   => 'Book\Controller\BookController',
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
    'router.routes.book' => array(
        'type'                => 'segment',
        'options.route'       => '/book[/:action][/:id]',
        'options.constraints' => array(
            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'id'     => '[0-9]+',
        ),
        'options.defaults' => array(
            'controller' => 'Book\Controller\Book',
            'action'     => 'index',
        ),
    ),

    'view_manager.template_path_stack.album' => __DIR__ . '/../view',
    'view_manager.template_path_stack.book' => __DIR__ . '/../view',
));
```

[1]: http://docs.mongodb.org/manual/core/document/#dot-notation
[2]: http://framework.zend.com/
