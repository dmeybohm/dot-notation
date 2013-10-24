<?php

namespace ZfConfigTest;

use ZfConfig\ZfConfig;

class ZfConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that configuration can be loaded from a file.
     *
     * @return void
     */
    public function testConfigurationCanBeLoadedFromAFile()
    {
        $config = ZfConfig::fromFile(__DIR__ . '/../data/test_config.php');
        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }
    
    /**
     * Tests that dots are expanded to appropriate array keys.
     *
     * @return void
     */
    public function testDotsAreExpandedToAppropriateArrayKeys()
    {
        $config = ZfConfig::expand(array(
            'foo' => array(
                'bar' => array('buzz'),
            ),
            'foo.bar.baz' => array('foo'),
        ));
        $expect = array(
            'foo' => array(
                'bar' => array(
                    'buzz',
                    'baz' => array('foo'),
                ),
            ),
        );
        $this->assertEquals($expect, $config);
    }

    /**
     * Tests that typical global.php configuration works
     *
     * @return void
     */
    public function testTypicalGlobalPHPConfigurationWorks()
    {
        $config = ZfConfig::expand(array(
            'db.driver' => 'Pdo',
            'db.dsn'    => 'mysql:dbname=zf2tutorial;host=localhost',
            'db.driver_options' => array(
                '__xxx_PDO::MYSQL_ATTR_INIT_COMMAND' => 'SET NAMES \'UTF8\''
            ),
            'service_manager.factories' => array(
                'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory'
            ),
        ));
        $expect = array(
            'db' => array(
                'driver'         => 'Pdo',
                'dsn'            => 'mysql:dbname=zf2tutorial;host=localhost',
                'driver_options' => array(
                    '__xxx_PDO::MYSQL_ATTR_INIT_COMMAND' => 'SET NAMES \'UTF8\''
                ),
            ),
            'service_manager' => array(
                'factories' => array(
                    'Zend\Db\Adapter\Adapter'
                            => 'Zend\Db\Adapter\AdapterServiceFactory',
                ),
            )
        );

        $this->assertEquals($expect, $config);
    }
    
    /**
     * Tests that typical module configuration works.
     *
     * @return void
     */
    public function testTypicalModuleConfigurationWorks()
    {
        $config = ZfConfig::expand(array(
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

        $expect = array(
            'view_manager' => array(
                'template_path_stack' => array(
                    'album' => __DIR__ . '/../view',
                ),
            ),

            'controllers' => array(
                'invokables' => array(
                    'Album\Controller\Album' => 'Album\Controller\AlbumController',
                ),
            ),

            // The following section is new and should be added to your file
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

        $this->assertEquals($expect, $config);
    }
    
    /**
     * Tests that keys can be overridden when specified multiple times.
     *
     * @return void
     */
    public function testKeysCanBeOverriddenWhenSpecifiedMultipleTimes()
    {
        $config = ZfConfig::expand(array(
            'foo.bar.baz' => 'Foo',
            'foo.bar' => array(
                'baz' => 'Qux'
            ),
        ));
        $expect = array('foo' => array(
            'bar' => array(
                'baz' => 'Qux',
            ),
        ));
        $this->assertEquals($expect, $config);
    }

    /**
     * Tests that keys that are not arrays are not appended to when specified multiple times.
     *
     * @return void
     */
    public function testKeysThatAreNotArraysAreNotAppendedToWhenSpecifiedMultipleTimes()
    {
        $config = ZfConfig::expand(array(
            'view_manager' => array(
                'template_path_stack' => array(
                    'album' => __DIR__ . '/../view1',
                ),
            ),
            'view_manager.template_path_stack.album' => __DIR__ . '/../view2',
        ));
        $expect = array(
            'view_manager' => array(
                'template_path_stack' => array(
                    'album' => __DIR__ . '/../view2',
                ),
            ),
        );
        $this->assertEquals($expect, $config);
    }

    /**
     * Tests that keys that have backslashes before dots are not expanded.
     *
     * @return void
     */
    public function testKeysThatHaveAnOddNumberOfBackslashesBeforeDotsAreNotExpanded_ButOneIsRemoved()
    {
        $config = ZfConfig::expand(array(
            'view_manager' => array(
                'template_path_stack' => array(
                    'album' => __DIR__ . '/../view1',
                ),
            ),
            'view_manager\.template_path_stack\.album' => __DIR__ . '/../view2',
        ));
        $expect = array(
            'view_manager' => array(
                'template_path_stack' => array(
                    'album' => __DIR__ . '/../view1',
                ),
            ),
            'view_manager.template_path_stack.album' => __DIR__ . '/../view2',
        );
        $this->assertEquals($expect, $config, 'One backslash failed!');

        $config = ZfConfig::expand(array(
            'view_manager' => array(
                'template_path_stack' => array(
                    'album' => __DIR__ . '/../view1',
                ),
            ),
            'view_manager\\\.template_path_stack\\\.album' => __DIR__ . '/../view2',
        ));
        $expect = array(
            'view_manager' => array(
                'template_path_stack' => array(
                    'album' => __DIR__ . '/../view1',
                ),
            ),
            'view_manager\.template_path_stack\.album' => __DIR__ . '/../view2',
        );

        $this->assertEquals($expect, $config, 'Three backslashes failed!');
    }
    
    /**
     * Tests that keys with an even number of backslashes are expanded, but the number is halved.
     *
     * @return void
     */
    public function testKeysThatHaveAnEvenNumberOfBackslashesBeforeDotsAreNotExpanded_ButTheNumberIsHalved()
    {
        $config = ZfConfig::expand(array(
            'view_manager' => array(
                'template_path_stack' => array(
                    'album' => __DIR__ . '/../view1',
                ),
            ),
            'view_manager\\.template_path_stack\\.album' => __DIR__ . '/../view2',
        ));
        $expect = array(
            'view_manager' => array(
                'template_path_stack' => array(
                    'album' => __DIR__ . '/../view1',
                ),
            ),
            'view_manager.template_path_stack.album' => __DIR__ . '/../view2',
        );
        $this->assertEquals($expect, $config, 'Two backslashes failed!');

        $config = ZfConfig::expand(array(
            'view_manager\\' => array(
                'template_path_stack\\' => array(
                    'album' => __DIR__ . '/../view1',
                ),
            ),
            'view_manager\\\\.template_path_stack\\\\.album' => __DIR__ . '/../view2',
        ));
        $expect = array(
            'view_manager\\' => array(
                'template_path_stack\\' => array(
                    'album' => __DIR__ . '/../view1',
                ),
            ),
            'view_manager\\.template_path_stack\\.album' => __DIR__ . '/../view2',
        );
        $this->assertEquals($expect, $config, 'Four backslashes failed!');
    }

    /**
     * Tests that backslashes that do not preceed a period in a key name do not require escaping.
     *
     * @return void
     */
    public function testBackslashesThatDoNotPreceedAPeriodInAKeyNameDoNotRequireEscaping()
    {
        $config = ZfConfig::expand(array(
            'loaded.classes' => array(
                'Zend\Loader\ClassMapAutoloader' => array(
                    __DIR__ . '/autoload_classmap.php',
                ),
            )
        ));
        $expect = array(
            'loaded' => array(
                'classes' => array(
                    'Zend\Loader\ClassMapAutoloader' => array(
                        __DIR__ . '/autoload_classmap.php',
                    )
                )
            )
        );
        $this->assertEquals($expect, $config, "Backslashes are not being left alone when not before period!");
    }
    
}