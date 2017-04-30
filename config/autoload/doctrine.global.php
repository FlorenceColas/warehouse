<?php
namespace Warehouse;

/**
 * Created by PhpStorm.
 * User: FlorenceColas
 * Date: 02/02/16
 * Time: 14:10
 */

return [
    'doctrine' => [
        'driver' => [
            __NAMESPACE__.'Driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    //'../src/'.__NAMESPACE__.'/Entity',
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

/*    'doctrine' => array(
        'driver' => array(
            'app_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/Application/Entity'
                )
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Application\Entity' => 'app_driver'
                )
            )
        ),
    )
  */
];