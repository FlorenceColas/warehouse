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
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zend\Session\Container;

class StockMergementRepository extends EntityRepository
{
    public function getStockByCriterias(array $criterias = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('A.id, A.description, A.netquantity, B.unit')
            ->from('\Warehouse\Entity\StockMergement', 'A')
            ->leftJoin('\Warehouse\Entity\MeasureUnit', 'B', \Doctrine\ORM\Query\Expr\Join::WITH, 'B.id = A.measureunit');

        if (null != $criterias) {
            if (isset($criterias['description'])) {
                $qb->andWhere($qb->expr()->like('A.description', $qb->expr()->literal('%' . $criterias['description'] . '%')));
            }
            if (isset($criterias['area'])) {
                $qb->andWhere('A.area = ?1')
                    ->setParameter(1, $criterias['area']);
            }
            if (isset($criterias['status'])) {
                $qb->andWhere('A.status = ?2')
                    ->setParameter(2, $criterias['status']);
            }
            if (isset($criterias['sections']) and count($criterias['sections']) > 0) {
                $qb->andWhere($qb->expr()->in('A.section', $criterias['sections']));
            }
        }

        $qb->orderBy('A.description', 'ASC');
        $query = $qb->getQuery()->getArrayResult();

        return $query;
    }

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
                case \Warehouse\Controller\StockmergementController::ON_STOCK:
                    $where = $where . ' and s.netquantity > 0';
                    break;
                case \Warehouse\Controller\StockmergementController::NOT_ON_STOCK:
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