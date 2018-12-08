<?php

namespace Warehouse\Factory;

use Interop\Container\ContainerInterface;
use Warehouse\Controller\ShoppingController;
use Zend\ServiceManager\Factory\FactoryInterface;

class ShoppingControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $doctrine = $container->get('doctrine.entitymanager.orm_default');
        $config = $container->get('config');

        return new ShoppingController($doctrine, $config);
    }
}
