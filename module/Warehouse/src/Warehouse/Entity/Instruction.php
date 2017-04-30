<?php
/**
 * User: FlorenceColas
 * Date: 28/03/16
 * Version: 1.00
 * Instruction: Entity corresponding to instructions table
 * Properties:
 *      - id
 *      - description
 *      - sequence
 *      - recipe (recipe_id)
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\InstructionRepository")
 * @ORM\Table(name="instructions")
 */
class Instruction
{
    /**
     * @ORM\Id 
	 * @ORM\Column(type="integer") 
	 * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string")
     */
    protected $description;
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $sequence;
	/**
     * @ORM\ManyToOne(targetEntity="Recipe", inversedBy="instructions")
     * @ORM\JoinColumn(name="recipe_id", referencedColumnName="id", nullable=false)
     */
    private $recipe;

	/**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

	/**
     * @return integer
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param integer $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * @return Recipe
     */
    public function getRecipe()
    {
        return $this->recipe;
    }

    /**
     * @param Recipe $recipe
     */
    public function setRecipe($recipe)
    {
        $this->recipe = $recipe;
    }

}