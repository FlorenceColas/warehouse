<?php

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
    }

    /**
     * Settings
     * @return Settings
     */
    public function getFindSettings()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
            ->from('\Warehouse\Entity\Appsettings', 'a')
            ->orderBy('a.settingreference');
        $query = $qb->getQuery()->getArrayResult();

        return $query;
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