<?php
namespace Application;

use Zend\Db\Adapter as DbAdapter;
use Zend\Expressive\Plates\PlatesRendererFactory;
use Zend\Expressive\Template\TemplateRendererInterface;

return [
    'AuditTrail',
    'Auth',
    'Common',

    'console' => [
        'router' => [
            'routes' => [
            ],
        ],
    ],

    'controllers' => [
        'invokables' => [
//            'Rest\Service'                        => 'Rest\ServiceController',
            'Warehouse\Controller\Management'     => 'Warehouse\Controller\ManagementController',
            'Warehouse\Controller\Settings'       => 'Warehouse\Controller\SettingsController',
            'Warehouse\Controller\Tools'          => 'Warehouse\Controller\ToolsController',
        ],
        'factories' => [
            'Warehouse\Controller\Attachment'          => 'Warehouse\Factory\AttachmentControllerFactory',
            'Warehouse\Controller\Inventory'           => 'Warehouse\Factory\InventoryControllerFactory',
            'Warehouse\Controller\Inventoryattachment' => 'Warehouse\Factory\InventoryattachmentControllerFactory',
            'Warehouse\Controller\Recipe'              => 'Warehouse\Factory\RecipeControllerFactory',
            'Warehouse\Controller\Recipeattachment'    => 'Warehouse\Factory\RecipeattachmentControllerFactory',
            'Warehouse\Controller\Shopping'            => 'Warehouse\Factory\ShoppingControllerFactory',
            'Warehouse\Controller\Stockinterface'      => 'Warehouse\Factory\StockinterfaceControllerFactory',
            'Warehouse\Controller\Stockmergement'      => 'Warehouse\Factory\StockMergementControllerFactory',
        ]

    ],

    'form_elements' => [
        'invokables' => [
            'stockform' => 'Warehouse\Form\StockForm',
            'barcode' => 'Warehouse\Form\Element\Barcode',
        ],
    ],

    'templates'       => [
        'extension' => 'phtml',
        'paths'     => [
            'recipe' => [__DIR__ . '/../templates/recipe'],
        ],
    ],

    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
//            'Zend\Log\LoggerAbstractServiceFactory',
        ],
        'invokables' =>  [
        ],
        'factories' => [
            'navigation'                    => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'translator'                    => 'Zend\Mvc\Service\TranslatorServiceFactory',
//            'Zend\Db\Adapter\Adapter'       => 'Zend\Db\Adapter\AdapterServiceFactory',
            TemplateRendererInterface::class => PlatesRendererFactory::class,
        ],
        'shared' => [

        ],
    ],

    'router' => [
        'routes' => [
            'inventory' => [
                'type' => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/warehouse/inventory/list',
                    'defaults' => [
                        'controller' => 'Warehouse\Controller\Inventory',
                        'action'     => 'list',
                    ],
                ],
            ],
            'stockmerge'=> [
                'type' => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/warehouse/stockmergement/list',
                    'defaults' => [
                        'controller' => 'Warehouse\Controller\Stockmergement',
                        'action'     => 'list',
                    ],
                ],
            ],
            'stockinterface'=> [
                'type' => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/warehouse/stockinterface/list',
                    'defaults' => [
                        'controller' => 'Warehouse\Controller\Stockinterface',
                        'action'     => 'list',
                    ],
                ],
            ],
            'recipe' => [
                'type' => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/warehouse/recipe/list',
                    'defaults' => [
                        'controller' => 'Warehouse\Controller\Recipe',
                        'action'     => 'list',
                    ],
                ],
            ],

            'settings' => [
                'type' => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/warehouse/settings/list',
                    'defaults' => [
                        'controller' => 'Warehouse\Controller\Settings',
                        'action'     => 'list',
                    ],
                ],
            ],

            'management' => [
                'type' => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/warehouse/management/add',
                    'defaults' => [
                        'controller' => 'Warehouse\Controller\Management',
                        'action'     => 'add',
                    ],
                ],
            ],

            'shopping' => [
                'type' => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/warehouse/shopping/list',
                    'defaults' => [
                        'controller' => 'Warehouse\Controller\Shopping',
                        'action'     => 'list',
                    ],
                ],
            ],



            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'warehouse' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/warehouse',
                    'defaults' => [
                        '__NAMESPACE__' => 'Warehouse\Controller',
                        'controller'    => 'Auth',
                        'action'        => 'login',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/[:controller[/:action[/id/:id]]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                            ],
                        ],
                    ],
                    'paginator' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/[:controller[/:action[/page/:page]]]',
                            'constraints' => [
                                'page'     => '[1-9][0-9]*',
                            ],
                            'defaults' => [
                                'page'      => 1,
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
        ],
    ],

    'translator' => [
        'locale' => 'en_US',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],

    'view_helpers' => [
        'invokables'=> [
//            'PaginationHelper' => 'Warehouse\View\Helper\PaginationHelper',
            'fieldCollection' => 'Warehouse\View\Helper\FieldCollection',
            'fieldRow' => 'Warehouse\View\Helper\FieldRow'
        ]
    ],

    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/visitor'          => __DIR__ . '/../view/layout/visitor-layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            'warehouse' => __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
