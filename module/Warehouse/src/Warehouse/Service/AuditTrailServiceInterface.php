<?php
/**
 * User: FlorenceColas
 * Date: 28/01/2017
 * Version: 1.00
 * AuditTrailServiceInterface: Audit Trail service interface
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Service;

use Zend\Db\Sql\Ddl\Column\Blob;

interface AuditTrailServiceInterface
{
    /**
     * @param string $entity
     * @param string $controller
     * @param string $action
     * @param string $description
     * @return mixed
     */
    public function logEvent(string $entity, string $controller, string $action, string $description);
}