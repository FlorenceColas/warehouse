<?php
namespace Warehouse;

return [
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'datetime_functions' => [
                   'DATE_FORMAT'  => 'Warehouse\Tools\DQL\DateFormatFunction',
                ],
            ],
        ],
        'driver' => [
            __NAMESPACE__.'Driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    'module/'.__NAMESPACE__.'/src/'.__NAMESPACE__.'/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__.'\Entity' => __NAMESPACE__.'Driver',
                ],
            ],
        ],
    ],
];

