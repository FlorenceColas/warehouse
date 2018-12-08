<?php
namespace AuditTrail;

return [
    'db' => [
        'dbname'         => 'warehouse-v2',
        'driver'         => 'pdo_mysql',
        'driver_options' => [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8",
        ],
    ],

    'service_manager' => [
        'factories' => [
            AuditTrailService::class        => AuditTrailServiceFactory::class,
            \Zend\Db\Adapter\Adapter::class => \Zend\Db\Adapter\AdapterServiceFactory::class,
        ],
    ],
];
