<?php
/**
 * User: FlorenceColas
 * Date: 05/12/16
 * Version: 1.00
 * AppsettingsRepository: Repository for appsettings table. It contains the following functions:
 *      - findByReference: return the value of the reference in parameter
 *      - getPagedSettings: for pagination
 *      - findBySettingId: return the setting corresponding to the setting id in parameter
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AppsettingsRepository extends EntityRepository
{
    /**
     * Return the value of the reference in parameter
     * @param string $reference
     * @return array
     */
    public function findByReference($reference) {
        $result = $this->findBy(array('settingreference' => $reference));
        return $result;

/*        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('a')
            ->from('\Warehouse\Entity\Appsettings', 'a')
            ->where('a.settingreference=\''.$reference.'\'');
        $query = $qb->getQuery()->getResult();

        return $query;
*/
    }

    /**
     * Settings pagination
     * @param int $offset
     * @param int $limit
     * @param string $table
     * @return Paginator
     */
    public function getPagedSettings($offset = 0, $limit = 8, $table)
    {
        $entity = '\Warehouse\Entity\Appsettings';

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
            ->from($entity, 'a')
            ->orderBy('a.settingreference')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        $query = $qb->getQuery();

        $paginator = new Paginator($query);

        return $paginator;
    }

    /**
     * Return the setting corresponding to the setting id in parameter
     * @param int $id $table
     * @return Setting
     */
    public function findBySettingId($id, $table) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('a')
            ->from('\Warehouse\Entity\Appsettings', 'a')
            ->where('a.id='.$id);
        $query = $qb->getQuery()->getResult();
        return $query;
    }

}