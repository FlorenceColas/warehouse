<?php

namespace Auth\Service;

use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class AuthServiceFactory
 * @package Auth\Service
 */
class AuthServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AuthService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config     = $container->get('config');
        $dbAdapter  = new Adapter($config['db']);
        $storage    = $container->get('Auth\Storage\MyAuthStorage');
        $auditTrail = $container->get(\AuditTrail\AuditTrailService::class);

        return new AuthService($dbAdapter, $storage, $auditTrail);
    }
}
