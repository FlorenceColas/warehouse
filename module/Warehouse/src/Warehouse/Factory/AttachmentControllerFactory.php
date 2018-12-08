<?php

namespace Warehouse\Factory;

use Interop\Container\ContainerInterface;
use Warehouse\Controller\AttachmentController;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class AttachmentControllerFactory
 * @package Warehouse\Factory
 */
class AttachmentControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AttachmentController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config   = $container->get('config');
        $doctrine = $container->get('doctrine.entitymanager.orm_default');

        return new AttachmentController($config, $doctrine);
    }
}
