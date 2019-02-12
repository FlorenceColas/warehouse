<?php
namespace Warehouse;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Permissions\Acl\AclInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Helper\Navigation\AbstractHelper;
use Zend\View\Model\ViewModel;
use Zend\Permissions\Acl\Exception\ExceptionInterface as AclException;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this,'checkAuth'), -10);
        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this,'injectViewVariables'), 100);
    }

    public function getConfig()
    {
        $config        = include APPLICATION_PATH . '/config/module.config.php';
        $sessionConfig = include APPLICATION_PATH . '/config/session.config.php';
        $config        = ArrayUtils::merge($config, $sessionConfig);

        $authConfig = include APPLICATION_PATH . '/../Auth/config/ConfigProvider.php';
        $config     = ArrayUtils::merge($config, $authConfig);

        $auditTrailConfig = include APPLICATION_PATH . '/../AuditTrail/config/ConfigProvider.php';
        $config     = ArrayUtils::merge($config, $auditTrailConfig);

        $commonConfig = include APPLICATION_PATH . '/../Common/config/ConfigProvider.php';
        $config       = ArrayUtils::merge($config, $commonConfig);

        $auditTrailConfig = include APPLICATION_PATH . '/../AuditTrail/config/ConfigProvider.php';
        return ArrayUtils::merge($config, $auditTrailConfig);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => APPLICATION_PATH . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Check if the user is logged in with a valid session (not timed out), if not he is redirected to the login page
     * @param MvcEvent $event
     */
    public function checkAuth(MvcEvent $event)
    {
        $sm = $event->getApplication()->getServiceManager();
        $authService = $sm->get(\Auth\Service\AuthService::class);
        $match = $event->getRouteMatch();

        if (!$authService->hasIdentity() || !$authService->sessionIsValid()) {
            if ($match->getParam('controller') != \Auth\AuthController::class) {
                $match->setParam('controller', \Auth\AuthController::class);
                $match->setParam('action', 'login');
            }
        }

        if (!$match) {
            $match->setParam('controller', 'Warehouse\Controller\Success');
            $match->setParam('action', 'index');
        }

        //check if the authenticated user is authorised to access this page
 //       $this->checkAcl($event);
    }

    private function checkAcl(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        if (!$match) {
            return;
        }

        $controller = $match->getParam('controller');
        $action = $match->getParam('action');
        $namespace = $match->getParam('__NAMESPACE__');

        $parts = explode('\\', $namespace);
        $moduleNamespace = $parts[0];

        //get the role of the current user
        $sm = $event->getApplication()->getServiceManager();

        $authService = $sm->get('AuthService');
        $role = '';
        if ($authService->hasIdentity() && $authService->sessionIsValid()) {
            $role = $authService->getStorage()->getRoleLabel();
        }
        if ($role == '') {
            $role = $sm->get('config')['user']['role']['guest']['label'];
        }

        $acl = $sm->get('AclService');

        if ($acl instanceof AclInterface) {
            // This is how we add default acl and role to the navigation view helpers
            AbstractHelper::setDefaultAcl($acl);
            AbstractHelper::setDefaultRole($role);

            // check if the current module wants to use the ACL
            $aclModules = $sm->get('config')['acl']['modules'];

            if (!empty($aclModules) && !in_array($moduleNamespace, $aclModules)) {
                return;
            }

            //use alias as resource
            $resourceAliases = $sm->get('config')['acl']['resource_alias'];
            if (isset($resourceAliases[$controller])) {
                $resource = $resourceAliases[$controller];
            } else {
                $resource = strtolower(substr($controller, strrpos($controller,'\\')+1));
            }

            // add the resource in the ACL
            if(!$acl->hasResource($resource)) {
                $acl->addResource($resource);
            }

            try {
                if($acl->isAllowed($role, $resource, $action)) {
                    return;
                }
            } catch(AclException $ex) {
                // @todo: log in the warning log the missing resource
            }

            // Set the response code to HTTP 403: Forbidden
            $response = $event->getResponse();
            $response->setStatusCode(403);
            // and redirect the current user to the denied action
            $match->setParam('controller', 'Warehouse\Controller\Success');
            $match->setParam('action', 'denied');

            if ($resource != 'success' || $action != 'denied' || $resource != 'error' || $action != 'error') {
                $auditTrail = $sm->get(\AuditTrail\AuditTrailService::class);
                $auditTrail->logEvent('ACL', $resource, $action, 'The user "'.$authService->getStorage()->getName().'" (id: '.$authService->getStorage()->getId().') with the "'.$role.'" role tried to access the URL "'.$resource.'/'.$action.'"". Access denied.');
            }
        }
    }

    /**
     * Injects common variables in the view model
     * @param MvcEvent $event
     */
    public function injectViewVariables(MvcEvent $event)
    {
        $viewModel = $event->getViewModel();
        $sm = $event->getApplication()->getServiceManager();
        $variables = array();
        if ($sm->has('AuthService')) {
            $auth= $sm->get('AuthService');
            $variables['auth'] = $auth;
        }
        $variables['version'] = $sm->get('config')['version'];
        $variables['author'] = $sm->get('config')['author'];
        $variables['navigation'] = $sm->get('config')['navigation'];

        if (!empty($variables)) {
            $viewModel->setVariables($variables);
        }
    }

    /**
     * Injects ACL in the view model
     * @param MvcEvent $event
     */
    public function injectAcl(MvcEvent $event)
    {
        if(!$event->getResponse()->getContent()) {
            $sm = $event->getApplication()->getServiceManager();
            $viewModel = $event->getResult();
            if($viewModel instanceof ViewModel) {
                $viewModel->setVariable('acl', $sm->get('AclService'));
            }
        }
    }
}
