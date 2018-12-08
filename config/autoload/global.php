<?php
return [
    'author' => 'Florence Colas',

    'path' => [
        'application_path'           => APPLICATION_PATH,
        'recipe_image_upload'        => APPLICATION_PATH . '/public/upload/recipe',
        'recipe_public_thumb_upload' => APPLICATION_PATH . '/public/upload/recipe/thumb',
        'recipe_public_pdf'          => APPLICATION_PATH . '/public/recipes',
        'shopping_list_html'         => APPLICATION_PATH . '/public/shoppinglist/html',
        'shopping_list_pdf'          => APPLICATION_PATH . '/public/shoppinglist/pdf',
        'shopping_list_xls'          => APPLICATION_PATH . '/public/shoppinglist/xls',
        'stock_image_upload'         => APPLICATION_PATH . '/public/upload/stock',
        'stock_public_thumb_upload'  => APPLICATION_PATH . '/public/upload/stock/thumb',
        'wkhtmltopdf'                => '/usr/local/bin/wkhtmltopdf',
        'tmp'                        => APPLICATION_PATH . '/data/tmp',
    ],

    'upload_max_size' => '4 MB',

    'url' => [
        'shopping_list_html' => '/shoppinglist/html',
        'shopping_list_pdf'  => '/shoppinglist/pdf',
        'shopping_list_xls'  => '/shoppinglist/xls',
        'stock_thumb'        => '/upload/stock/thumb',
        'recipe_thumb'       => '/upload/recipe/thumb',
        'recipe_pdf'         => '/pdf/recipe',
    ],

    'version' => '2.0.0',








    'access' => [
        'administrator' => 2,
        'member'        => 1,
        'visitor'       => 0,
    ],
    'app_settings_entry' => [
        'default_shopping_list_unit'  => 'default_shopping_list_unit_id',
        'last_barcode_auto_generated' => 'last_barcode_auto_generated',
    ],
    'article_availability' => [
        'all'                           => 0,
        'not_on_stock'                  => 2,
        'on_stock'                      => 1,
        'on_stock_require_manual_check' => 5,
        'under_critical_threshold'      => 4,
        'under_info_threshold'          => 3,
    ],
    'authorized_file_extension' => [
        'jpg',
        'jpeg',
        'JPG',
        'gif',
        'GIF',
    ],
    'shopping_list' => [
        'color' => [
            'bought' => 'HoneyDew',
            'to_buy' => 'MistyRose',
        ],
        'priority' => [
            'major' => 1,
            'minor' => 2,
        ],
        'status' => [
            'bought'     => 2,
            'new_to_buy' => 1,
            'to_buy'     => 0,
        ],
    ],
    'user' => [
        'role' => [
            'admin' => [
                'code'  => 1,
                'label' => 'Admin',
                'name'  => 'admin',
            ],
            'guest' => [
                'code' => 3,
                'label' => 'Guest',
                'name'  => 'guest',
            ],
            'user' => [
                'code'  => 2,
                'label' => 'User',
                'name'  => 'user',
            ],
        ],
        'status' => [
            'blocked' => 2,
            'disabled' => 0,
            'enabled' => 1,
        ]
    ],



    'acl' => [
        'role' => [
            'admin' => null,
            'guest' => null,
            'user' => null,
        ],
        'resource' => [
            'auth' => null,
            'error' => null,
            'recipe' => null,
            'settings' => null,
            'stock' => null,
            'success' => null,
        ],
        'allow' => [
            [
                'admin',  //admin role can do anything
                null,
                null
            ],

            [
                'user',
                'auth',
                [
                    'login',
                    'loginhelp',
                    'logout',
                ],
            ],
            [
                'user',
                'success',
                [
                    'changeLanguage',
                    'changePassword',
                    'index',
                ],
            ],
            [
                'user',
                'recipe',
                [
                    'add',
                    'display',
                    'edit',
                    'list',
                ],
            ],
            [
                'user',
                'error',
                'error'
            ],
            [
                'guest',
                'auth',
                [
                    'login',
                    'loginhelp',
                    'logout',
                ]
            ],
            [
                'guest',
                'success',
                [
                    'changeLanguage',
                    'contact',
                    'index',
                ]
            ],
            [
                'guest',
                'recipe',
                [
                    'display',
                ],
            ],
        ],
        'deny'  => [
            [
                'guest',
                'success',
                'changepassword',
            ],
            [
                'guest',
                null, // null as second parameter means all resources
                'delete',
            ],
        ],
        'resource_alias' => [
            'Warehouse\Controller\Auth'     => 'auth',
            'Warehouse\Controller\Success'  => 'success',
            'Warehouse\Controller\Recipe'   => 'recipe',
            'Warehouse\Controller\Stock'    => 'stock',
            'Warehouse\Controller\Settings' => 'settings',
        ],
        'modules' => [
            'Warehouse',
        ],
    ],

    'navigation' => [
        'logout' => [
            'label' => 'Logout',
            'params' => [
                'action'     => 'logout',
                'controller' => 'auth',
            ],
            'permissions' => [ 1, 2, 3, ],
            'route' => 'application/default',
        ],
        'recipelist' => [
            'label' => 'Recipes List',
            'params' => [
                'action'     => 'list',
                'controller' => 'recipe',
            ],
            'permissions' => [ 1, 2, ],
            'route' => 'warehouse/default',
        ],
        'userlist' => [
            'label' => 'Users List',
            'params' => [
                'action'     => 'list',
                'controller' => 'user',
            ],
            'permissions' => [ 1, ],
            'route' => 'warehouse/default',
        ],
        'welcome' => [
            'label' => 'Welcome',
            'params' => [
                'action'        => 'index',
                'controller'    => 'success',
            ],
            'permissions' => [ 1, 2, 3, ],  //see ['user']['role'] for the values
            'route' => 'warehouse/default',
        ],
    ],
];
