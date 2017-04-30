<?php
/**
 * User: FlorenceColas
 * Date: 28/03/16
 * Version: 1.00
 * Ingredient: Entity corresponding to ingredients table
 * Properties:
 *      - id
 *      - description
 *      - quantity
 *      - recipe (recipe_id)
 *      - stock (stock_id)
 *      - unit (unit_id)
 *      - sequence
 *      - availability
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\IngredientRepository")
 * @ORM\Table(name="ingredients")
 */
class Ingredient
{
    /**
     * @ORM\Id 
	 * @ORM\Column(type="integer") 
	 * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string",nullable= true)
     */
    protected $description;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=2)
     */
    protected $quantity;
	 /**
     * @ORM\ManyToOne(targetEntity="Recipe", inversedBy="ingredients")
     * @ORM\JoinColumn(name="recipe_id", referencedColumnName="id", nullable=false)
     */
    private $recipe;
    /**
     * @ORM\ManyToOne(targetEntity="StockMergement")
     * @ORM\JoinColumn(name="merge_id", referencedColumnName="id", nullable=true)
     */
    protected $stockmergement;
    /**
     * @ORM\ManyToOne(targetEntity="MeasureUnit")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=false)
     */
    protected $unit;
    /**
     * @ORM\Column(type="integer")
     */
    protected $sequence;

    protected $availability;

	/**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
//        $recipe->addIngredients($this);
        $this->recipe = $recipe;
    }

    /**
     * @return decimal
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param decimal $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return StockMergement
     */
    public function getStockmergement()
    {
        return $this->stockmergement;
    }

    /**
     * @param StockMergement $stockmergement
     */
    public function setStockmergement($stockmergement)
    {
        $this->stockmergement = $stockmergement;
    }

    /**
     * @return MeasureUnit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param MeasureUnit $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
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
     * @return integer
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @param integer $availabitiliy
     */
    public function setAvailability($availabitiliy)
    {
        $this->availability = $availabitiliy;
    }
}