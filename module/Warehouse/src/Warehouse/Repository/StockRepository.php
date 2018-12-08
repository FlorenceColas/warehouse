<?php

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;
use Warehouse\Entity\Stock;

class StockRepository extends EntityRepository
{
    /**
     * @param array|null $criterias
     * @return Stock[]
     */
    public function getStockByCriterias(array $criterias = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('A.id, A.description, A.status, A.quantity, A.netquantity, A.infothreshold, A.criticalthreshold, C.unit, E.filename')
            ->from('\Warehouse\Entity\Stock', 'A')
            ->leftJoin('\Warehouse\Entity\StockMergement' , 'B', \Doctrine\ORM\Query\Expr\Join::WITH, 'B.id = A.stockmergement')
            ->leftJoin('\Warehouse\Entity\MeasureUnit'    , 'C', \Doctrine\ORM\Query\Expr\Join::WITH, 'C.id = B.measureunit')
            ->leftJoin('\Warehouse\Entity\StockAttachment', 'D', \Doctrine\ORM\Query\Expr\Join::WITH, 'D.stock_id = A.id')
            ->leftJoin('\Warehouse\Entity\Attachment'     , 'E', \Doctrine\ORM\Query\Expr\Join::WITH, 'E.id = D.attachment_id and E.defaultphoto=1');
        /*
                select a.*,c.* from stock a
        left join stock_attachment b on b.stock_id = a.id
        left join attachment c on c.id = b.attachment_id and c.defaultphoto=1
        where a.id=297
                */

        if (null != $criterias) {
            if (isset($criterias['description'])) {
                $qb->andWhere($qb->expr()->like('A.description', $qb->expr()->literal('%' . $criterias['description'] . '%')));
            }
            if (isset($criterias['area'])) {
                $qb->andWhere('B.area = ?1')
                    ->setParameter(1, $criterias['area']);
            }
            if (isset($criterias['status'])) {
                $qb->andWhere('A.status = ?2')
                    ->setParameter(2, $criterias['status']);
            }
            if (isset($criterias['sections']) and count($criterias['sections']) > 0) {
                $qb->andWhere($qb->expr()->in('B.section', $criterias['sections']));
            }
        }

        $qb->orderBy('A.description', 'ASC');
        $query = $qb->getQuery()->getArrayResult();

        return $query;
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
            ->where('s.stockmergement='.$mergeId);
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
            ->where('s.stockmergement='.$merge)
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
            ->where('s.stockmergement='.$merge)
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
            ->where('s.stockmergement is not null')
            ->orderBy('s.stockmergement,s.prefered,s.description');
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
            ->where('s.stockmergement='.$merge . ' and s.id!='.$id);
        $query = $qb->getQuery()->getResult();

        return $query;
    }


}
