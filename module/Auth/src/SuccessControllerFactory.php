<?php

namespace Auth;

use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class SuccessControllerFactory
 * @package Auth
 */
class SuccessControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return SuccessController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $auditTrail  = $container->get(\AuditTrail\AuditTrailService::class);
        $storage     = $container->get('Auth\Storage\MyAuthStorage');
        $config      = $container->get('config');
        $dbAdapter   = new Adapter($config['db']);

        return new SuccessController($auditTrail, $storage, $dbAdapter);
    }
}
