<?php
/**
 * User: FlorenceColas
 * Date: 27/02/16
 * Version: 1.00
 * InstructionRepository: Repository for instructions table. It contains the following functions:
 *      - findByInstructionId: return the instructions corresponding to the ingredient id in parameter
 *      - findByRecipeId: return the list of instructions for the recipe id in parameter
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;
use Warehouse\Entity\Instruction;

class InstructionRepository extends EntityRepository
{
    /**
     * Return the instructions corresponding to the ingredient id in parameter
     * @param int $id
     * @return Instruction
     */
    public function findByInstructionId($id) {
        $result = $this->findBy(array('id' => $id));
        return $result;
    }

    /**
     * Return the list of instructions for the recipe id in parameter
     * @param $id
     * @return Instruction[]
     */
    public function findByRecipeId($id){
        $result = $this->findBy([
            'recipe' => $id
        ]);
        return $result;
    }
}