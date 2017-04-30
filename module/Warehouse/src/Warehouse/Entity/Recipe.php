<?php
/**
 * User: FlorenceColas
 * Date: 13/11/16
 * Version: 1.00
 * Recipe: Entity corresponding to recipes table
 * Properties:
 *      - id
 *      - description
 *      - serves
 *      - preparationTime
 *      - totalTime
 *      - ingredients (collection of ingredient table)
 *      - instructions (collection of instruction table)
 *      - tags (collection of tag table)
 *      - note
 *      - category (category_id)
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\RecipeRepository")
 * @ORM\Table(name="recipes")
 */
class Recipe
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
    protected $serves;
    /**
     * @ORM\Column(type="time")
     */
    protected $preparationTime;
    /**
     * @ORM\Column(type="time")
     */
    protected $totalTime;
	
    /**
     * @ORM\OneToMany(targetEntity="Ingredient", mappedBy="recipe", cascade={"persist","remove"})
     * @var ArrayCollection Ingredient[]
     * @ORM\OrderBy({"sequence" = "ASC"})  //allow to order ingredients array
     **/
    private $ingredients;
    /**
     * @ORM\OneToMany(targetEntity="Instruction", mappedBy="recipe", cascade={"persist","remove"})
     * @var ArrayCollection Instruction[]
     * @ORM\OrderBy({"sequence" = "ASC"})  //allow to order instructions array
     **/
    private $instructions;
    /**
     * @ORM\OneToMany(targetEntity="Tag", mappedBy="recipe", cascade={"persist","remove"})
     * @var ArrayCollection Tag[]
     **/
    private $tags;
    /**
     * @ORM\Column(type="string")
     */
    protected $note;
    /**
     * @ORM\ManyToOne(targetEntity="RecipeCategory")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     */
    protected $category;
    /**
     * @ORM\OneToMany(targetEntity="RecipeAttachment", mappedBy="recipe", cascade={"persist","remove"})
     * @var ArrayCollection RecipeAttachment[]
     **/
    protected $recipeattachments;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
		$this->instructions = new ArrayCollection();
		$this->tags = new ArrayCollection();
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

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
    public function getServes()
    {
        return $this->serves;
    }

    /**
     * @param integer $serves
     */
    public function setServes($serves)
    {
        $this->serves = $serves;
    }

    /**
     * @return
     */
    public function getPreparationTime()
    {
        return $this->preparationTime;
    }

    /**
     * @param $preparationTime
     */
    public function setPreparationTime($preparationTime)
    {
        $this->preparationTime = $preparationTime;
    }

    /**
     * @param  $totalTime
     */
    public function setTotalTime($totalTime)
    {
        $this->totalTime = $totalTime;
    }

    /**
     * @return
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }

    /**
     * @param Ingredient $ingredient
     */
    public function addIngredients(Ingredient $ingredient)
	{
        $this->ingredients[] = $ingredient;
        $ingredient->setRecipe($this);
        return $this;
    }

    /**
     * @param Ingredient $ingredient
     */
    public function removeIngredients(Ingredient $ingredient)
    {
        $this->ingredients->removeElement($ingredient);
    }

    /**
     * @return Ingredient[]
     */
    public function getIngredients()
    {
        return $this->ingredients;
    }

	/**
	 * @param Instruction $instruction
	 */
	public function addInstructions(Instruction $instruction)
	{
		$this->instructions[] = $instruction;
        $instruction->setRecipe($this);
        return $this;
	}

    /**
     * @param Instruction $instruction
     */
    public function removeInstructions(Instruction $instruction)
    {
        $this->instructions->removeElement($instruction);
    }

	/**
	 * @return Instruction[]
	 */
	public function getInstructions()
	{
		return $this->instructions;
	}

	/**
	 * @param Tag $tag
	 */
	public function addTags($tag) 
	{
		$this->tags[] = $tag;
	}

	/**
	 * @return Tag[]
	 */
	public function getTags()
	{
		return $this->tags;
	}

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return RecipeCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param RecipeCategory $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @param RecipeAttachment $recipeattachment
     */
    public function addRecipeAttachment(RecipeAttachment $recipeattachment)
    {
        $this->recipeattachments[] = $recipeattachment;
        $recipeattachment->setRecipe($this);
        return $this;
    }

    /**
     * @param RecipeAttachment $recipeattachment
     */
    public function removeRecipeAttachment(Attachment $recipeattachment)
    {
        $this->recipeattachments->removeElement($recipeattachment);
    }

    /**
     * @return RecipeAttachment[]
     */
    public function getRecipeAttachment()
    {
        return $this->recipeattachments;
    }

}
