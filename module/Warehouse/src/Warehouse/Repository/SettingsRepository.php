<?php

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Warehouse\Enum\EnumTableSettings;

class SettingsRepository extends EntityRepository
{
    /**
     * Return all the settings (for the table in parameter) order by description
     * @param string $table
     * @return Settings[]
     */
    public function findAllOrderByDescription($table) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        if ($table == EnumTableSettings::SECTION) {
            $qb->select('s')
                ->from('\Warehouse\Entity\\' . $table, 's')
                ->orderBy('s.area,s.description');
        } else {
            $qb->select('s')
                ->from('\Warehouse\Entity\\' . $table, 's')
                ->orderBy('s.description');
        }
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return the settings with area equals to the value in parameter
     * @param string $table $area
     * @return Settings[]
     */
    public function findByAreaOrderDescription($table, $area) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('s')
                ->from('\Warehouse\Entity\\' . $table, 's')
                ->where('s.area='.$area)
                ->orderBy('s.description');
        $query = $qb->getQuery()->getResult();

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
        $qb->select('s')
            ->from('\Warehouse\Entity\\' . $table, 's')
            ->where('s.id='.$id);
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return the measure unit available in stock form
     * @return Setting
     */
    public function findAvailableStockUnit() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('m')
            ->from('\Warehouse\Entity\MeasureUnit', 'm')
            ->where('m.useinstock=1');
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Settings
     * @param string $table
     * @return Settings
     */
    public function getFindSettings($table)
    {
        $entity = '\Warehouse\Entity\\'.$table;

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from($entity, 's')
            ->orderBy('s.description');
        $query = $qb->getQuery()->getArrayResult();

        return $query;
    }
}