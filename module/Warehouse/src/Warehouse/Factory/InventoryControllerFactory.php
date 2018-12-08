<?php

namespace Warehouse\Factory;

use Interop\Container\ContainerInterface;
use Warehouse\Controller\InventoryController;
use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\Factory\FactoryInterface;

class InventoryControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config   = $container->get('config');
        $doctrine = $container->get('doctrine.entitymanager.orm_default');
        $adapter  = new Adapter($config['db']);

        return new InventoryController($config, $doctrine, $adapter);
    }
}
