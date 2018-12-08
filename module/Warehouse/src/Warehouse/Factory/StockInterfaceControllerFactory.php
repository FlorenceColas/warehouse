<?php

namespace Warehouse\Factory;

use Interop\Container\ContainerInterface;
use Warehouse\Controller\StockinterfaceController;
use Zend\ServiceManager\Factory\FactoryInterface;

class StockInterfaceControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $doctrine = $container->get('doctrine.entitymanager.orm_default');

        return new StockinterfaceController($doctrine);
    }
}
