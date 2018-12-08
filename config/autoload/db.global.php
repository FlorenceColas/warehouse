<?php
return [
    'db' => [
        'driver' => 'pdo_mysql',
        'driver_options' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8",
        ],
    ],
];
