<?php
/**
 * User: FlorenceColas
 * Date: 04/02/16
 * Version: 1.00
 * StockRepository: Repository for stock table. It contains the following functions:
 *      - getPagedStock: stock list pagination
 *      - findAllOrderByDescription: return all the stock
 *      - findAllForAreaOrderByDescription: return all the stock for the area in parameter
 *      - findByStockId: return the stock corresponding to the stock id in parameter
 *      - findCountQuantityForMergeId: return the total quantity corresponding to the merge_id in parameter
 *      - findByMergeId: return the stock list corresponding to the merge id in parameter
 *      - findByPreferedMergeId: return the stock list corresponding to the prefered merge id in parameter
 *      - findAllMergedOrderByMergePreferedDescription: return all the stock with a merger
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *      - 11/02/2017: add findCountQuantityForMergeId function
 *
 */

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Warehouse\Enum\EnumAvailability;
use Warehouse\Enum\EnumStatus;
use Warehouse\Model\CriteriaStock;
use Warehouse\Entity\Stock;
use Zend\Session\Container;

class StockRepository extends EntityRepository
{
    /**
     * Stock list pagination
     * @param int $offset
     * @param int $limit
     * @param CriteriaStock $criteriaStock
     * @return Paginator
     */
    public function getPagedStock($offset = 0, $limit = 50, $criteriaStock)
    {
        $where = '';
        $inventorySession = new Container('InventorySearch');

        if ($inventorySession->offsetExists('status')) {
            $where = 's.status = ' . $inventorySession->status;
        }
        else {
            $where = 's.status = ' . EnumStatus::Enabled;
        }

/*        if ($inventorySession->offsetExists('section'))
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
                    $where = $where . ' and s.section in (' . $sectionValues . ')';
            }
        }
*/
        if (!is_null($criteriaStock)) {
            if ($criteriaStock->getDescription() != '') {
                $where = $where . ' and s.description like \'%' . $criteriaStock->getDescription() . '%\'';
            }
        }

/*        if ($inventorySession->offsetExists('area'))
            $area = $inventorySession->area;
        else if (!is_null($criteriaStock))
            $area = $criteriaStock->getArea();
        if (isset($area)) {
            $where = $where . ' and s.area = ' . $area;
        }
*/
        if ($inventorySession->offsetExists('availability'))
            $availability = $inventorySession->availability;
        else if (!is_null($criteriaStock))
            $availability = $criteriaStock->getAvailability();
        if (isset($availability)) {
            switch ($availability) {
                case EnumAvailability::OnStock:
                    $where = $where . ' and s.quantity > 0';
                    break;
                case EnumAvailability::NotOnStock:
                    $where = $where . ' and s.quantity = 0';
                    break;
                case EnumAvailability::UnderInfoThreshold:
                    $where = $where . ' and s.quantity <= s.infothreshold';
                    break;
                case EnumAvailability::UnderCriticalThreshold:
                    $where = $where . ' and s.quantity <= s.criticalthreshold';
                    break;
            }
        }

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\Stock', 's')
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
     * Return all the stock
     * @return Stock[]
     */
    public function findAllOrderByDescription() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\Stock', 's')
            ->orderBy('s.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return all the stock for an area in parameter
     * @param $area
     * @return Stock[]
     */
    public function findAllForAreaOrderByDescription($area) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\Stock', 's')
            ->where('s.area='.$area)
            ->orderBy('s.section,s.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return the stock corresponding to the stock id in parameter
     * @param int $id
     * @return Stock
     */
    public function findByStockId($id) {
        $result = $this->findBy(array('id' => $id));
        return $result;
    }

    /**
     * Return the total quantity corresponding to the merge_id in parameter
     * @param $mergeId
     * @return int
     */
    public function findCountQuantityForMergeId($mergeId){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('sum(s.quantity * s.netquantity) as quantity')
            ->from('\Warehouse\Entity\Stock', 's')
            ->where('s.merge='.$mergeId);
        $query = $qb->getQuery()->getResult();
        return $query;
    }

    /**
     * @param $merge
     * @return array
     */
    public function findByMergeId($merge) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\Stock', 's')
            ->where('s.merge='.$merge)
            ->orderBy('s.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * @param $merge
     * @return array
     */
    public function findByPreferedMergeId($merge) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\Stock', 's')
            ->where('s.merge='.$merge)
            ->orderBy('s.prefered,s.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return all the stock with merger
     * @return Stock[]
     */
    public function findAllMergedOrderByMergePreferedDescription() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\Stock', 's')
            ->where('s.merge is not null')
            ->orderBy('s.merge,s.prefered,s.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return all article with the merger in parameter, except the current article in parameter too
     * @param $merge, $id
     * @return array
     */
    public function findByAllMergeIdExceptCurrent($merge, $id) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\Stock', 's')
            ->where('s.merge='.$merge . ' and s.id!='.$id);
        $query = $qb->getQuery()->getResult();

        return $query;
    }


}
