<?php
namespace Application;

use Warehouse\Controller\ManagementController;
use Warehouse\Controller\ToolsController;
use Warehouse\Factory\AttachmentControllerFactory;
use Warehouse\Factory\InventoryattachmentControllerFactory;
use Warehouse\Factory\InventoryControllerFactory;
use Warehouse\Factory\RecipeattachmentControllerFactory;
use Warehouse\Factory\RecipeControllerFactory;
use Warehouse\Factory\SettingsControllerFactory;
use Warehouse\Factory\ShoppingControllerFactory;
use Warehouse\Factory\StockInterfaceControllerFactory;
use Warehouse\Factory\StockMergementControllerFactory;
use Warehouse\Form\Element\BarCode;
use Warehouse\Form\StockForm;
use Warehouse\View\Helper\FieldCollection;
use Warehouse\View\Helper\FieldRow;
use Zend\Db\Adapter as DbAdapter;
use Zend\Expressive\Plates\PlatesRendererFactory;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\I18n\Translator\TranslatorServiceFactory;

return [
    'AuditTrail',
    'Auth',
    'Common',

    'controllers' => [
        'invokables' => [
            'Warehouse\Controller\Management' => ManagementController::class,
            'Warehouse\Controller\Tools'      => ToolsController::class,
        ],
        'factories' => [
            'Warehouse\Controller\Attachment'          => AttachmentControllerFactory::class,
            'Warehouse\Controller\Inventory'           => InventoryControllerFactory::class,
            'Warehouse\Controller\Inventoryattachment' => InventoryattachmentControllerFactory::class,
            'Warehouse\Controller\Recipe'              => RecipeControllerFactory::class,
            'Warehouse\Controller\Recipeattachment'    => RecipeattachmentControllerFactory::class,
            'Warehouse\Controller\Settings'            => SettingsControllerFactory::class,
            'Warehouse\Controller\Shopping'            => ShoppingControllerFactory::class,
            'Warehouse\Controller\Stockinterface'      => StockInterfaceControllerFactory::class,
            'Warehouse\Controller\Stockmergement'      => StockMergementControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'invokables' => [
            'stockform' => StockForm::class,
            'barcode'   => BarCode::class,
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
                ],
            ],
        ],
    ],

    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
        ],
        'factories' => [
            //       'navigation'                    => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'translator'                    => TranslatorServiceFactory::class,
            TemplateRendererInterface::class => PlatesRendererFactory::class,
        ],
    ],

    'templates'       => [
        'extension' => 'phtml',
        'paths'     => [
            'recipe' => [__DIR__ . '/../templates/recipe'],
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
            'fieldCollection' => FieldCollection::class,
            'fieldRow'        => FieldRow::class,
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
