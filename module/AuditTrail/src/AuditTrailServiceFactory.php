<?php

namespace AuditTrail;

use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class AuditTrailServiceFactory
 * @package AuditTrail
 */
class AuditTrailServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AuditTrailService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AuditTrailService
    {
        $storage   = $container->get('Auth\Storage\MyAuthStorage');
        $config    = $container->get('config');
        $dbAdapter = new Adapter($config['db']);

        return new AuditTrailService($storage, $dbAdapter);
    }
}
