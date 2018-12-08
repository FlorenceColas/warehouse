<?php

namespace Auth\Service;

use AuditTrail\AuditTrailService;
use Auth\Storage\MyAuthStorage;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;

class AuthService extends AuthenticationService implements AdapterInterface
{
    const USER_STATUS_ENABLED = 1;
    const USER_STATUS_DISABLED = 0;
    const USER_STATUS_BLOCKED = 2;

    protected $auditTrail;
    protected $dbAdapter;
    protected $storage;

    public function __construct (
        DbAdapter $dbAdapter,
        MyAuthStorage $storage,
        AuditTrailService $auditTrail
    ) {
        parent::__construct();

        $this->dbAdapter  = $dbAdapter;
        $this->storage    = $storage;
        $this->auditTrail = $auditTrail;

        $dbTableAuthAdapter = new CredentialTreatmentAdapter(
            $this->dbAdapter,
            'user',
            'logonName',
            'password',
            'md5(?)'
        );
        $dbTableAuthAdapter->getDbSelect()->where('status = ' . $this::USER_STATUS_ENABLED);

        $this->setAdapter($dbTableAuthAdapter);
        //definited custom storage
        $this->setStorage($this->storage);
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
                $this->storage->setRememberMe(1);
            }

            $sql = new Sql($this->dbAdapter);
            $select = $sql->select()
                ->columns(
                    [
                        'access',
                        'id',
                        'logonName',
                        'name',
                    ]
                )
                ->from('user')
                ->where([
                    'logonName' => $logon,
                ]);

            $statement = $sql->prepareStatementForSqlObject($select);
            $user      = iterator_to_array($statement->execute());

            $this->storage->setLogonName($user[0]['logonName']);
            $this->storage->setId($user[0]['id']);
            $this->storage->setName($user[0]['name']);
            $this->storage->setAccess($user[0]['access']);

            // update the lastconnection field
            $sql    = new Sql($this->dbAdapter);
            $update = $sql->update('user')
                ->set([
                    'lastconnection' => (new \DateTime())->format('Y-m-d H:i:s'),
                ])
                ->where([
                    'logonName' => $logon,
                ]);

            $statement = $sql->buildSqlString($update);
            $result    = $this->dbAdapter->query($statement, DbAdapter::QUERY_MODE_EXECUTE);

            //action traceability
            $this->auditTrail->logEvent('Login', 'Auth', 'Login', 'The user '.$this->storage->getLogonName().' has successfully connected');

            $this->storage->setAuthenticationExpirationTime();
            //set storage again
            $this->setStorage($this->storage);
            return ['valid' => true, 'message' => []];
        } else {
            //action traceability
            $this->auditTrail->logEvent('Login', 'Auth', 'Login', 'Authentication FAILED using the logonname '.$logon);
            return ['valid' => false, 'message' => [$result->getMessages()]];
        }
    }

    public function sessionIsValid()
    {
        if ($this->storage->isExpiredAuthenticationTime()) {
            $this->auditTrail->logEvent('Session', 'Check', 'Valid', 'Session timeout for the user '.$this->storage->getLogonName());
            $this->storage->clear();
            return false;
        } else {
            $this->storage->setAuthenticationExpirationTime();
            return true;
        }
    }

    public function logOut()
    {
        $this->auditTrail->logEvent('Login', 'Auth', 'Logout', 'The user '.$this->storage->getLogonName(). ' has logged out');
        $this->storage->clear();
    }
}
