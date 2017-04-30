<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Warehouse;

use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Stdlib\ArrayUtils;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $eventManager->attach('dispatch', array($this, 'loadConfiguration' ));

        //initialize session manager
      //  $this->initializeSession($e);
    }

    public function loadConfiguration(MvcEvent $e)
    {
        $controller = $e->getTarget();
        $config = include __DIR__ . '/config/module.config.php';
        $controller->layout()->VERSION = $config['version'];
    }

    public function getConfig()
    {
        $config = include __DIR__ . '/config/module.config.php';
        $sessionConfig = include __DIR__ . '/config/session.config.php';
        return  ArrayUtils::merge($config,$sessionConfig);
    }

/*    public function getConfig()
    {
        $config = include __DIR__ . '/config/module.config.php';
        $sessionConfig = include __DIR__ . '/config/session.config.php';
        return ArrayUtils::merge($config,$sessionConfig);

    }
*/
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

/*    public function initializeSession($em)
    {
        $config = $em->getApplication()
            ->getServiceManager()
            ->get('Config');

        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['session_config']);

        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->start();
        $sessionManager->getValidatorChain()->attach( 'session.validate', array( new \Zend\Session\Validator\HttpUserAgent(), 'isValid'));
        $sessionManager->getValidatorChain()->attach( 'session.validate', array( new \Zend\Session\Validator\RemoteAddr(), 'isValid'));

        Container::setDefaultManager($sessionManager); //in case to manage many SessionManagers
    }
*/

 /*   public function init(ModuleManager $mm)
    {
        $mm->getEventManager()->getSharedManager()->attach(__NAMESPACE__,
            'dispatch', function($e) {
                $e->getTarget()->layout('warehouse/attachment/upload');
            });
    }
 */
}
