<?php
/**
 * User: FlorenceColas
 * Date: 18/02/16
 * Version: 1.00
 * RecipeAttachmentRepository: Repository for recipeattachment table. It contains the following functions:
 *      - getPagedRecipe: recipe list pagination
 *      - findByRecipeId: return the recipe corresponding to the recipe id in parameter
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Warehouse\Entity\Recipe;
use Warehouse\Enum\EnumSession;
use Warehouse\Model\CriteriaRecipe;
use Zend\Session\Container;

class RecipeRepository extends EntityRepository
{
    /**
     * Recipe list pagination
     * @param int $offset
     * @param int $limit
     * @param  $criteriaRecipe
     * @param CriteriaRecipe $criteriaRecipe
     * @return Paginator
     */
    public function getPagedRecipe($offset = 0, $limit = 50, $criteriaRecipe)
    {
        $where = '';
        $recipeSession = new Container(EnumSession::RECIPESEARCH);

        if ($recipeSession->offsetExists(EnumSession::RECIPESEARCH_CATEGORY))
            $category = $recipeSession->category;
        else if (!is_null($criteriaRecipe))
            $category = $criteriaRecipe->getCategory();

        if (isset($category)) {
            if (sizeof($category) != 0) {
                $categoryValues = '';
                foreach ($category as $c) {
                    if ($categoryValues != '')
                        $categoryValues = $categoryValues . ',' . $c;
                    else
                        $categoryValues = $c;
                }
                if ($categoryValues != '')
                    $where = ' r.category in (' . $categoryValues . ')';
            }
        }

        if (!is_null($criteriaRecipe)) {
            if ($criteriaRecipe->getDescription() != '') {
                if ($where != '')
                    $where = $where . ' and r.description like \'%' . $criteriaRecipe->getDescription() . '%\'';
                else
                    $where = ' r.description like \'%' . $criteriaRecipe->getDescription() . '%\'';
            }
        }

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('r')
            ->from('\Warehouse\Entity\Recipe', 'r')
            ->orderBy('r.description')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        if ($where != '') {
            $qb->where($where);
        }

        //var_dump('query:'.$qb);
        $query = $qb->getQuery();
        $paginator = new Paginator($query);

        return $paginator;
    }

    /**
     * Return the recipe corresponding to the recipe id in parameter
     * @param int $id
     * @return Recipe
     */
    public function findByRecipeId($id) {
        $result = $this->findBy(array('id' => $id));
        return $result;
    }
}
