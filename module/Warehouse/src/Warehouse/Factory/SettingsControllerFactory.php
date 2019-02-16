<?php

namespace Warehouse\Factory;

use Interop\Container\ContainerInterface;
use Warehouse\Controller\AttachmentController;
use Warehouse\Controller\SettingsController;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class SettingsControllerFactory
 * @package Warehouse\Factory
 */
class SettingsControllerFactory implements FactoryInterface
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

        return new SettingsController($config, $doctrine);
    }
}
