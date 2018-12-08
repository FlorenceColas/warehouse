<?php

namespace AuditTrail;

use Auth\Storage\MyAuthStorage;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Adapter\Driver\Pdo\Result;
use Zend\Db\Sql\Sql;
use Zend\Http\PhpEnvironment\RemoteAddress;

/**
 * Class AuditTrailService
 * @package AuditTrail
 */
class AuditTrailService
{
    /**
     * @var DbAdapter
     */
    protected $dbAdapter;
    /**
     * @var MyAuthStorage
     */
    protected $storage;

    /**
     * @param MyAuthStorage $storage
     * @param DbAdapter $dbAdapter
     */
    public function __construct (
        MyAuthStorage $storage,
        DbAdapter $dbAdapter
    ) {
        $this->dbAdapter = $dbAdapter;
        $this->storage   = $storage;
    }

    /**
     * @param string $entity
     * @param string $controller
     * @param string $action
     * @param string $description
     * @return Result
     */
    public function logEvent(string $entity, string $controller, string $action, string $description): Result
    {
        $remote = new RemoteAddress();

        if ($this->storage->getLogonName() != null) {
            $user = $this->storage->getLogonName();
        } else {
            $user = 'DefaultAdmin';
        }

        $sql = new Sql($this->dbAdapter);
        $insert = $sql->insert('audittrail')
            ->values([
                'action'      => $action,
                'controller'  => $controller,
                'datetime'    => (new \DateTime())->format('Y-m-d H:i:s'),
                'description' => $description,
                'entity'      => $entity,
                'ip'          => $remote->setUseProxy()->getIpAddress(),
                'user'        => $user,
            ]);

        $statement = $sql->buildSqlString($insert);
        $result    = $this->dbAdapter->query($statement, DbAdapter::QUERY_MODE_EXECUTE);

        return $result;
    }
}
