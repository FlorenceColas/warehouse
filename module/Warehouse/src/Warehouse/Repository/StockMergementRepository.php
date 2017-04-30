<?php
/**
 * User: FlorenceColas
 * Date: 22/01/2017
 * Version: 1.00
 * StockMergementRepository: Repository for stockmergement table. It contains the following functions:
 *      - findAllOrderByDescription: return all StockMergement records order by description
 *      - findByStockMergementId: return the StockMergement corresponding to the id in parameter
 *      - getPagedStock: for list pagination
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;
use Warehouse\Enum\EnumAvailability;
use Warehouse\Enum\EnumStatus;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zend\Session\Container;

class StockMergementRepository extends EntityRepository
{
    /**
     * Stock list pagination
     * @param int $offset
     * @param int $limit
     * @param CriteriaStockMergement $criteriaStock
     * @return Paginator
     */
    public function getPagedStock($offset = 0, $limit = 50, $criteriaStock)
    {
        $where = '';
        $inventorySession = new Container('StockSearch');

        if ($inventorySession->offsetExists('section'))
            $section = $inventorySession->section;
        else if (!is_null($criteriaStock))
            $section = $criteriaStock->getSection();

        if (isset($section)) {
            if (sizeof($section) != 0) {
                $sectionValues = '';
                foreach ($section as $s) {
                    if ($sectionValues != '')
                        $sectionValues = $sectionValues . ',' . $s;
                    else
                        $sectionValues = $s;
                }
                if ($sectionValues != '')
                    $where = ' s.section in (' . $sectionValues . ')';
            }
        }

        if (!is_null($criteriaStock)) {
            if ($criteriaStock->getDescription() != '') {
                $where = $where . ' and s.description like \'%' . $criteriaStock->getDescription() . '%\'';
            }
        }

        if ($inventorySession->offsetExists('area'))
            $area = $inventorySession->area;
        else if (!is_null($criteriaStock))
            $area = $criteriaStock->getArea();
        if (isset($area)) {
            $where = $where . ' and s.area = ' . $area;
        }

        if ($inventorySession->offsetExists('availability'))
            $availability = $inventorySession->availability;
        else if (!is_null($criteriaStock))
            $availability = $criteriaStock->getAvailability();
        if (isset($availability)) {
            switch ($availability) {
                case EnumAvailability::OnStock:
                    $where = $where . ' and s.netquantity > 0';
                    break;
                case EnumAvailability::NotOnStock:
                    $where = $where . ' and s.netquantity = 0';
                    break;
            }
        }

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\StockMergement', 's')
            ->orderBy('s.description')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        if ($where != '')
        {
            $qb->where($where);
        }
        $query = $qb->getQuery();

        $paginator = new Paginator($query);

        return $paginator;
    }

    /**
     * Return all StockMergement records order by description
     * @return StockMergement[]
     */
    public function findAllOrderByDescription(){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\StockMergement', 's')
            ->orderBy('s.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return the StockMergement corresponding to the id in parameter
     * @param int $id
     * @return StockMergement
     */
    public function findByStockMergementId($id) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('s')
            ->from('\Warehouse\Entity\StockMergement', 's')
            ->where('s.id='.$id);
        $query = $qb->getQuery()->getResult();
        return $query;
    }

}