<?php
namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\RecipeAttachmentRepository")
 * @ORM\Table(name="recipes_attachment")
 */
class RecipeAttachment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="Recipe", inversedBy="recipeattachments")
     * @ORM\JoinColumn(name="recipes_id", referencedColumnName="id", nullable=true)
     */
    private $recipes_id;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="Recipe", inversedBy="recipeattachments")
     * @ORM\JoinColumn(name="attachement_id", referencedColumnName="id", nullable=true)
     */
    private $attachment_id;

    /**
     * @return $recipes_id
     */
    public function getRecipesId()
    {
        return $this->recipes_id;
    }

    /**
     * @param integer $recipes_id
     */
    public function setRecipeId($recipes_id)
    {
        $this->recipes_id = $recipes_id;
    }

    /**
     * @return $attachment_id
     */
    public function getAttachmentId()
    {
        return $this->attachment_id;
    }

    /**
     * @param integer $attachment_id
     */
    public function setAttachmentId($attachment_id)
    {
        $this->attachment_id = $attachment_id;
    }

}