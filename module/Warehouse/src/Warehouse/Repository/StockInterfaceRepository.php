<?php
/**
 * User: FlorenceColas
 * Date: 22/01/2017
 * Version: 1.00
 * StockInterfaceRepository: Repository for stockinterface table. It contains the following functions:
 *      - findAllOrderByDescription: return all StockInterface records order by description
 *      - findByStockInterfaceId: return the StockInterface corresponding to the id in parameter
 *      - findAllOrderBySensDescription: return all StockInterface records order by sens,description
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;

class StockInterfaceRepository extends EntityRepository
{
    /**
     * Return all StockInterface records order by description
     * @return StockInterface[]
     */
    public function findAllOrderByDescription(){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\StockInterface', 's')
            ->orderBy('s.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return the StockInterface corresponding to the id in parameter
     * @param int $id
     * @return StockInterface
     */
    public function findByStockInterfaceId($id) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('s')
            ->from('\Warehouse\Entity\StockInterface', 's')
            ->where('s.id='.$id);
        $query = $qb->getQuery()->getResult();
        return $query;
    }

    /**
     * Return all StockInterface records order by sens, description
     * @return StockInterface[]
     */
    public function findAllOrderBySensDescription(){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\StockInterface', 's')
            ->orderBy('s.sens,s.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }

}