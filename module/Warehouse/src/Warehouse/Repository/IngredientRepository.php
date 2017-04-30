<?php
/**
 * User: FlorenceColas
 * Date: 27/02/16
 * Version: 1.00
 * IngredientRepository: Repository for ingredients table. It contains the following functions:
 *      - findByIngredientId: return the ingredient corresponding to the ingredient id in parameter
 *      - findByRecipeId: return the list of ingredients for the recipe id in parameter
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;
use Warehouse\Entity\Ingredient;

class IngredientRepository extends EntityRepository
{
    /**
     * Return the ingredient corresponding to the ingredient id in parameter
     * @param int $id
     * @return Ingredient
     */
    public function findByIngredientId($id) {
        $result = $this->findBy([
            'id' => $id]
        );
        return $result;
    }

    /**
     * Return the list of ingredients for the recipe id in parameter
     * @param $id
     * @return Ingredient[]
     */
    public function findByRecipeId($id){
        $result = $this->findBy([
            'recipe' => $id
        ]);
        return $result;
    }
}