<?php
/**
 * User: FlorenceColas
 * Date: 30/01/2017
 * Version: 1.00
 * AuditTrailServiceFactory: Audit Trail service factory
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Warehouse\Service\AuditTrailService;

class AuditTrailServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AuditTrailService($serviceLocator);
    }
}