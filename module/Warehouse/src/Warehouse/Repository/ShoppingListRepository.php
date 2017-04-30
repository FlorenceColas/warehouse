<?php
/**
 * User: FlorenceColas
 * Date: 02/04/16
 * Version: 1.00
 * ShoppingListRepository: Repository for shoppinglist tables. It contains the following functions:
 *      - findByShoppingListId: return the article in the shopping list corresponding to the shopping list id in parameter
 *      - findAllOrderBySectionDescription: return the entire shopping list order by area, section, description
 *      - findByStockMergementId: return the stock in the shopping list corresponding to the stock id in parameter
 *      - findByAll: return the shopping list content
 *      - findByAllSendToStock: return the shopping list content with sendtostock=1
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;
use Warehouse\Entity\ShoppingList;

class ShoppingListRepository extends EntityRepository
{
    /**
     * Return the article in the shopping list coresponding to the shopping list id in parameter
     * @param $id
     * @return ShoppingList[]
     */
    public function findByShoppingListId($id){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('s')
            ->from('\Warehouse\Entity\ShoppingList', 's')
            ->where('s.id='.$id);
        $query = $qb->getQuery()->getResult();
        return $query;
    }

    /**
     * Return the entire shopping list order by area, section, description
     * @return ShoppingList[]
     */
    public function findAllOrderBySectionDescription() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('\Warehouse\Entity\ShoppingList', 's')
            ->orderBy('s.recipe,s.area,s.section,s.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return the article in the shopping list corresponding to the stock id in parameter
     * @param $mergeid
     * @return ShoppingList
     */
    public function findByStockMergementId($mergeid){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('s')
            ->from('\Warehouse\Entity\ShoppingList', 's')
            ->where('s.stockmergement='.$mergeid.' and s.recipe is null');
        $query = $qb->getQuery()->getResult();
        return $query;
    }

    /**
     * Return the shopping list content
     * @return ShoppingList[]
     */
    public function findByAll(){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('s')
            ->from('\Warehouse\Entity\ShoppingList', 's')
            ->orderBy('s.area,s.section,s.description');
        $query = $qb->getQuery()->getResult();
        return $query;
    }

    /**
     * Return the shopping list content with sendtostock=1
     */
    public function findByAllSendToStock() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('s')
            ->from('\Warehouse\Entity\ShoppingList', 's')
            ->where('s.sendtostock=1');
        $query = $qb->getQuery()->getResult();
        return $query;
    }

}