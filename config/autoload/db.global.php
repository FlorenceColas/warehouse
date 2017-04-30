<?php
/**
 * Created by PhpStorm.
 * User: FlorenceColas
 * Date: 02/02/16
 * Time: 14:13
 */

return array(

    'db' => array(
        'driver' => 'pdo_mysql',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"
        ),
    ),

);
