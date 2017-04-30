<?php
/**
 * User: FlorenceColas
 * Date: 30/01/2017
 * Version: 1.00
 * AuditTrailService: Audit Trail service
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Service;

use Warehouse\Entity\AuditTrail;
use Doctrine\ORM\EntityManager;
use Zend\Http\PhpEnvironment\RemoteAddress;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuditTrailService implements AuditTrailServiceInterface
{
    protected $entityManager;
    protected $serviceLocator;
    protected $storage;

    public function __construct (ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param string $entity
     * @param string $controller
     * @param string $action
     * @param string $description
     */
    public function logEvent(string $entity, string $controller, string $action, string $description)
    {
        $em = $this->getEntityManager();
        $storage = $this->getSessionStorage();
        $auditTrail = new AuditTrail();
        if ($storage->getLogonName() != null)
            $auditTrail->setUser($storage->getLogonName());
        else
            $auditTrail->setUser('DefaultAdmin');
        $auditTrail->setDatetime(new \DateTime());
        $auditTrail->setEntity($entity);
        $auditTrail->setAction($action);
        $auditTrail->setController($controller);
        $auditTrail->setDescription($description);
        $remote = new RemoteAddress();
        //    $message[]= $remote->setUseProxy()->getIpAddress().'<br>';
        //    $message[]= $this->getRequest()->getServer('REMOTE_ADDR').'<br>';
        $auditTrail->setIp($remote->setUseProxy()->getIpAddress());
        $em->persist($auditTrail);
        $em->flush();
    }

    private function setEntityManager(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->setEntityManager($this->serviceLocator->get('doctrine.entitymanager.orm_default'));
        }
        return $this->entityManager;
    }

    private function getSessionStorage()
    {
        if (! $this->storage) {
            $this->storage = $this->serviceLocator->get('Warehouse\Model\MyAuthStorage');
        }
        return $this->storage;
    }

}