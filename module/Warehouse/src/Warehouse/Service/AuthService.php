<?php
/**
 * User: FlorenceColas
 * Date: 31/01/2017
 * Version: 1.00
 * AuthService: authentication service. It contains:
 *      - authenticateUser: Authenticate the user and initialized his dedicated storage
 *      - isValid: Check if the user is authenticated and if his session is still active (not timeout)
 *      - logOut: logout the user
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Service;

use Warehouse\Enum\EnumUserStatus;
use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Zend\Session\Container;

class AuthService extends AuthenticationService implements AuthServiceInterface
{
    protected $serviceLocator;
    protected $entityManager;
    protected $audittrailservice;

    public function __construct (ServiceLocatorInterface $serviceLocator) {
        parent::__construct();
        $this->serviceLocator = $serviceLocator;
        //define the adapter
        $dbAdapter = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
        $dbTableAuthAdapter  = new CredentialTreatmentAdapter($dbAdapter);
        $dbTableAuthAdapter->setTableName('user')
            ->setIdentityColumn('logonName')
            ->setCredentialColumn('password')->setCredentialTreatment('md5(?)')
            ->getDbSelect()->where('status = '.EnumUserStatus::USER_STATUS_ENABLED);
        $this->setAdapter($dbTableAuthAdapter);
        //definited custom storage
        $this->setStorage($this->serviceLocator->get('Warehouse\Model\MyAuthStorage'));
    }

    /**
     * Authenticate the user and initialized his dedicated storage
     * @param  $logon
     * @param $password
     * @param  $rememberMe
     * @return boolean
     */
    public function authenticateUser($logon, $password, $rememberMe){
        $this->getAdapter()
            ->setIdentity($logon)
            ->setCredential($password);
        $result = $this->authenticate();
        if ($result->isValid()) {
            //check if it has rememberMe :
            if ($rememberMe == 1) {
                $this->getStorage()->setRememberMe(1);
            }
            //check the user right access
            $em = $this->getEntityManager();
            $user = $em->getRepository('Warehouse\Entity\User')->findByLogonName($logon);
            //fill in storage with user info
            $this->getStorage()->setLogonName($user[0]->getLogonName());
            $this->getStorage()->setId($user[0]->getId());
            $this->getStorage()->setName($user[0]->getName());
            $this->getStorage()->setAccess($user[0]->getAccess());
            //persist the last connection datetime in db
            $user[0]->setLastconnection(new \DateTime());
            $em->persist($user[0]);
            $em->flush();

            //action traceability
            $auditTrail = $this->getAuditTrailService();
            $auditTrail->logEvent('Login', 'Auth', 'Login', 'The user '.$this->getStorage()->getLogonName().' has successfully connected');

            $this->getStorage()->setAuthenticationExpirationTime();
            //set storage again
            $this->setStorage($this->getStorage());
            return ['valid' => true, 'message' => []];
        } else {
            //action traceability
            $auditTrail = $this->getAuditTrailService();
            $auditTrail->logEvent('Login', 'Auth', 'Login', 'Authentication FAILED using the logonname '.$logon);
            return ['valid' => false, 'message' => $result->getMessages()];
        }
    }

    /**
     * Check if the user session is still active (not timeout)
     * @return bool
     */
    public function sessionIsValid()
    {
        if ($this->getStorage()->isExpiredAuthenticationTime()) {
            //action traceability
            $auditTrail = $this->getAuditTrailService();
            $auditTrail->logEvent('Session', 'Check', 'Valid', 'Session timeout for the user '.$this->getStorage()->getLogonName());
            $session = new Container('warehouse');
            $session->getManager()->getStorage()->clear('warehouse');
            return false;
        } else {
            $this->getStorage()->setAuthenticationExpirationTime();
            return true;
        }
    }

    /**
     * Logout the user
     */
    public function logOut()
    {
        $auditTrail = $this->getAuditTrailService();
        $auditTrail->logEvent('Login', 'Auth', 'Logout', 'The user '.$this->getStorage()->getLogonName(). ' has logged out');
        $session = new Container('warehouse');
        $session->getManager()->getStorage()->clear('warehouse');
    }

    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->setEntityManager($this->serviceLocator->get('doctrine.entitymanager.orm_default'));
        }
        return $this->entityManager;
    }

    private function setEntityManager(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    private function getAuditTrailService()
    {
        if (! $this->audittrailservice) {
            $this->audittrailservice = $this->serviceLocator->get('AuditTrailService');
        }
        return $this->audittrailservice;
    }

}