<?php

namespace Warehouse\Factory;

use Interop\Container\ContainerInterface;
use Warehouse\Controller\StockmergementController;
use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\Factory\FactoryInterface;

class StockMergementControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config   = $container->get('config');
        $doctrine = $container->get('doctrine.entitymanager.orm_default');
        $adapter  = new Adapter($config['db']);

        return new StockmergementController($doctrine, $adapter, $config);
    }
}
