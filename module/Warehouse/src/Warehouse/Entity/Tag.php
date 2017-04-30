<?php
/**
 * User: FlorenceColas
 * Date: 03/02/16
 * Version: 1.00
 * Tag: Entity corresponding to tag table
 * Properties:
 *      - id
 *      - description
 *      - recipe (recipe_id)
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

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
     * @ORM\Column(type="string")
     */
    protected $description;
    /**
     * @ORM\ManyToOne(targetEntity="Recipe", inversedBy="tags")
     * @ORM\JoinColumn(name="recipe_id", referencedColumnName="id")
     */
    private $recipe;
    
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