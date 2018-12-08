<?php
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
     * @ORM\ManyToMany(targetEntity="Attachment")
     * @ORM\JoinTable(name="recipes_attachment",
     *      joinColumns={@ORM\JoinColumn (name="recipes_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="attachment_id", referencedColumnName="id")}
     *      )
     */
    private $attachment;
    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     */
    protected $category;
    /**
     * @ORM\OneToMany(targetEntity="Tag", mappedBy="recipe", cascade={"persist","remove"})
     * @var ArrayCollection Tag[]
     **/
    private $tags;
    /**
     * @ORM\Column(type="string")
    */
    protected $description;
    /**
     * @ORM\Column(type="string")
     */
    protected $note;
    /**
     * @ORM\Column(type="time")
     */
    protected $preparationTime;
    /**
     * @ORM\Column(type="integer")
     */
    protected $serves;
    /**
     * @ORM\Column(type="time")
     */
    protected $totalTime;

    public function __construct()
    {
        $this->attachment = new ArrayCollection();
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
        if (is_null($this->preparationTime)) {
            return '00:00';
        } else {
            return date_format($this->preparationTime, 'H:i');
        }
    }

    /**
     * @param $preparationTime
     */
    public function setPreparationTime($preparationTime)
    {
        if (!$preparationTime instanceof \DateTime) {
            $time = explode(':', $preparationTime);
            if (count($time) <= 1) {
                $time = explode(',', $preparationTime);
                if (count($time) > 1) {
                    $h = $time[0];
                    $s = $time[1] * 60 / 10;
                    $preparationTime = str_pad($h,2,'0',STR_PAD_LEFT) . ':' . str_pad($s,2,'0',STR_PAD_LEFT);
                } else {
                    $preparationTime = str_pad($preparationTime,2,'0',STR_PAD_LEFT) . ':00';
                }
            }
            $preparationTime = new \DateTime($preparationTime);
        }

        $this->preparationTime = $preparationTime;
    }

    /**
     * @param  $totalTime
     */
    public function setTotalTime($totalTime)
    {
        if (!$totalTime instanceof \DateTime) {
            $time = explode(':', $totalTime);
            if (count($time) <= 1) {
                $time = explode(',', $totalTime);
                if (count($time) > 1) {
                    $h = $time[0];
                    $s = $time[1] * 60 / 10;
                    $totalTime = str_pad($h,2,'0',STR_PAD_LEFT) . ':' . str_pad($s,2,'0',STR_PAD_LEFT);
                } else {
                    $totalTime = str_pad($totalTime,2,'0',STR_PAD_LEFT) . ':00';
                }
            }
            $totalTime = new \DateTime($totalTime);
        }

        $this->totalTime = $totalTime;
    }

    /**
     * @return
     */
    public function getTotalTime()
    {
        if (is_null($this->totalTime)) {
            return '00:00';
        } else {
            return date_format($this->totalTime, 'H:i');
        }
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
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }


    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->$attachment[] = $attachment;
        $attachment->setRecipe($this);
        return $this;
    }

    /**
     * @param Attachment $attachment
     */
    public function removeAttachment($attachment)
    {
        $this->$attachment->removeElement($attachment);
    }

    /**
     * @return Attachment[]
     */
    public function getAttachment()
    {
        return $this->attachment;
    }
}
