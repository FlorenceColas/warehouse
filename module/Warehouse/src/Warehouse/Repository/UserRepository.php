<?php
/**
 * User: FlorenceColas
 * Date: 09/02/16
 * Version: 1.00
 * UserRepository: Repository for user tables. It contains the following functions:
 *      - getPagedUsers: Users list pagination
 *      - findByLogonName: return the user corresponding to the logonname in parameter
 *      - findByUserId: returnn the user corresponding to the user id in parameter
 *      - findAllVisitor: return all visitors
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{

    /**
     * Recipe list pagination
     * @param int $offset
     * @param int $limit
     * @return Paginator
     */
    public function getPagedUsers($offset = 0, $limit = 50)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('u')
            ->from('\Application\Entity\User', 'u')
            ->orderBy('u.name')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $query = $qb->getQuery();
        $paginator = new Paginator($query);

        return $paginator;
    }

    /**
     * Return the user corresponding to the logonname in parameter
     * @param int $logonname
     * @return User
     */
    public function findByLogonName($logonName) {
        $result = $this->findBy(array('logonName' => $logonName));
        return $result;
    }

    /**
     * Return the user corresponding to the id in parameter
     * @param int $id
     * @return User
     */
    public function findByUserId($id) {
        $result = $this->findBy(array('id' => $id));
        return $result;
    }

    /**
     * Return all visitors
     * @return Users
     */
    public function findAllVisitor() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('u')
            ->from('\Application\Entity\User', 'u')
            ->where('u.access='.EnumUserAccess::UER_ACCESS_VISITOR .' and u.status='.EnumUserStatus::USER_STATUS_ENABLED);
        $query = $qb->getQuery()->getResult();

        return $query;
    }
}

?>