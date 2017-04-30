<?php
/**
 * User: FlorenceColas
 * Date: 31/01/2017
 * Version: 1.00
 * AuthServiceFactory: Authentication service factory
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Factory;

use Warehouse\Service\AuthService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AuthService($serviceLocator);
    }
}