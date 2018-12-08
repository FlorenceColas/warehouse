<?php
namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\TagRepository")
 * @ORM\Table(name="tag")
 */
 class Tag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
      * @ORM\ManyToOne(targetEntity="Recipe", inversedBy="tags")
      * @ORM\JoinColumn(name="recipes_id", referencedColumnName="id")
      */
     private $recipe;
    /**
     * @ORM\Column(type="string")
     */
    protected $description;

    /**
     * @return $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return $description
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
        $recipe->addTags($recipe);
        $this->recipe = $recipe;
    }
    
}

?>