<?php
namespace Auth;

return [
    'controllers' => [
        'factories' => [
            \Auth\AuthController::class    => \Auth\AuthControllerFactory::class,
            \Auth\SuccessController::class => \Auth\SuccessControllerFactory::class,
        ],
    ],

    'db' => [
        'dbname'         => 'warehouse-v2',
        'driver'         => 'pdo_mysql',
        'driver_options' => [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8",
        ],
    ],

    'router' => [
        'routes' => [
            'change_password' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/success[/:action[/id/:id]]',
                    'defaults' => [
                        'controller' => \Auth\SuccessController::class,
                        'action'     => 'changepassword',
                        '__NAMESPACE__' => 'Auth',
                    ],
                ],
            ],
            'login' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/auth/auth/login',
                    'defaults' => [
                        'controller'    => \Auth\AuthController::class,
                        'action'        => 'login',
                        '__NAMESPACE__' => 'Auth',
                    ],
                ],
            ],
            'login_help' => [
                'type'    => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/auth/auth/loginhelp',
                    'defaults' => [
                        'controller'    => \Auth\AuthController::class,
                        'action'        => 'loginhelp',
                        '__NAMESPACE__' => 'Auth',
                    ],
                ],
            ],
            'logout' => [
                'type'    => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/auth/auth/logout',
                    'defaults' => [
                        'controller'    => \Auth\AuthController::class,
                        'action'        => 'logout',
                        '__NAMESPACE__' => 'Auth',
                    ],
                ],
            ],
            'success' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/auth/auth/success',
                    'defaults' => [
                        'controller'    => \Auth\SuccessController::class,
                        'action'        => 'index',
                        '__NAMESPACE__' => 'Auth',
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            'Auth\Storage\MyAuthStorage'   => function($sm){
                $config = $sm->get('config');
                $authSessionStorage = new Storage\MyAuthStorage('warehouse');
                $authSessionStorage->setAllowedIdleTimeInSeconds($config['session']['config']['authentication_expiration_time']);
                return $authSessionStorage;
            },
            Service\AuthService::class     => Service\AuthServiceFactory::class,
        ],
    ],

    'session' => [
        'config' => [
            'authentication_expiration_time' => 1800,
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => [
                'cookie_httponly'     => true,
                'cookie_lifetime'     => 3600,
                'gc_maxlifetime'      => 3600,
                'name'                => 'warehouse',
                'remember_me_seconds' => 3600,
            ],
        ],
        'validators' => [
            'Zend\Session\Validator\HttpUserAgent',
            'Zend\Session\Validator\RemoteAddr',
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'layout/login'     => __DIR__ . '/../view/layout/login-layout.phtml',
            'layout/loginhelp' => __DIR__ . '/../view/layout/loginhelp-layout.phtml',
        ],
        'template_path_stack' => [
            'auth'      => __DIR__ . '/../../Auth/view',
        ],
    ],
];
