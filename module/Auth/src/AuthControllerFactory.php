<?php

namespace Auth;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class AuthControllerFactory
 * @package Auth
 */
class AuthControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AuthController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $auditTrail  = $container->get(\AuditTrail\AuditTrailService::class);
        $authService = $container->get(\Auth\Service\AuthService::class);
        $storage     = $container->get('Auth\Storage\MyAuthStorage');
        $config      = $container->get('config');

        return new AuthController($auditTrail, $authService, $storage, $config);
    }
}
