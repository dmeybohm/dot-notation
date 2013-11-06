# Dot Notation

This supports a syntax similar to [MongoDB's dot notation][1] for creating
deeply nested arrays compactly in PHP.

## Installation

Do:

```
php composer.phar require best/dot-notation:dev-master
```

## Usage

If you have your autoloader configured, you can just use the class.  The
interface consists of one static method: `expand()`. This method transforms the
dotted notation to the equivalent expanded array.

```php
<?php
use Best\DotNotation;

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

### Keys can be appended to

Dotted keys can be specified many times in the same array, and all the values will be 
appended as long as all of the parent keys are arrays.

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

### Overriding keys throws an exception

Switching from a non-array type to an array or vice-versa inside a
dotted key throws an exception.

```php
// this throws an exception of \Best\DotNotation\KeyAlreadyExistsException
$array = DotNotation::expand(array(
    'my.dotted.key' => 'value1',
    'my.dotted.key.subkey' => 'value2'
));
```

This should catch some mistakes when using the dot notation, but note that
PHP itself will happily override keys in the same array. So, the following
will not throw an error, but silently override the first key:

```php
$array = DotNotation::expand(array(
    'controllers.invokables' => array('Book\Controller\Book'),
    'controllers.invokables' => array('Album\Controller\Album'),
));
// expands to:
array('controllers' => array('invokables' => array('Album\Controller\Album')));
```

### Escaping dot to include it in keys

If you want to include a dot inside a key name, you can escape it with a backslash.

```php
use Best\DotNotation;
$array = DotNotation::expand(array('my\.dotted\.key' => 'value'));
// expands to: 
array('my.dotted.key' => 'value')
```

Backslash is not treated as a special character in any other case, though. So you
can use them in keys as long as it doesn't preceed a dot. Neither backslashes nor dots
in values are transformed in any way, either.

## Zend Framework 2 Configuration Example

Here's an example from inside the example [Zend Framework 2][2] configuration
files where the arrays are deeply nested. 

```php
return array(
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

Compared with the expanded version, note how much more readable the dot notation
version of the same configuration is in addition to being smaller:

```php
$array = DotNotation::expand(array(
    'controllers.invokables.Album\Controller\Album' => 'Album\Controller\AlbumController',

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
```

Because all keys are appended together, it's possible to have groups of related
configuration that can be grouped together into sections. Otherwise, you'd have
to have one place for all your controllers configuration, another for all your
routes, and so on:

```php
$array = DotNotation::expand(array(
    //
    // Configuration for the album controller:
    //
    'controllers.invokables.Album\Controller\Album' => 'Album\Controller\AlbumController',

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
    'controllers.invokables.Book\Controller\Book' => 'Book\Controller\BookController',

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

    'view_manager.template_path_stack' => array(
        'album' => __DIR__ . '/../view',
        'book' => __DIR__ . '/../view',
    ),
));
```

[1]: http://docs.mongodb.org/manual/core/document/#dot-notation
[2]: http://framework.zend.com/
