<?php
/**
 * User: FlorenceColas
 * Date: 07/03/16
 * Version: 1.00
 * SessionRepository: Repository for session table. It contains the following functions:
 *      - findBySessionId: return the session corresponding to the session id in parameter
 *      - findByUserId: return the session corresponding to the user id in parameter
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;
use Warehouse\Entity\Session;

class SessionRepository extends EntityRepository
{
    /**
     * Return the session corresponding to the session id in parameter
     * @param int $sessionId
     * @return Session
     */
    public function findBySessionId($sessionId) {
        $result = $this->findBy(array('id' => $sessionId));
        return $result;
    }

    /**
     * Return the session corresponding to the user id in parameter
     * @param int $userId
     * @return Session
     */
    public function findByUserId($userId) {
        $result = $this->findBy(array('userid' => $userId));
        return $result;
    }
}