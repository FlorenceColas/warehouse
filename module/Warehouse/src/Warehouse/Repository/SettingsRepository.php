<?php
/**
 * User: FlorenceColas
 * Date: 29/02/16
 * Version: 1.00
 * SettingsRepository: Repository for setting tables. It contains the following functions:
 *      - findAllOrderByDescription: return all the settings (for the table in parameter) order by description
 *      - findByAreaOrderDescription: return the settings with area equals to the value in parameter
 *      - findBySettingId: return the setting corresponding to the setting id in parameter
 *      - findAvailableStockUnit: return the measure unit available in stock form
 *      - getPagedSettings: settings pagination
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

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
     * Settings pagination
     * @param int $offset
     * @param int $limit
     * @param string $table
     * @return Paginator
     */
    public function getPagedSettings($offset = 0, $limit = 8, $table)
    {
        $entity = '\Warehouse\Entity\\'.$table;

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from($entity, 's')
            ->orderBy('s.description')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        $query = $qb->getQuery();

        $paginator = new Paginator($query);

        return $paginator;
    }
}