<?php
/**
 * User: FlorenceColas
 * Date: 03/02/16
 * Version: 1.00
 * RecipeCategory: Entity corresponding to recipecategory table
 * Properties:
 *      - id
 *      - description
 *      - area (area_id)
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\SettingsRepository")
 * @ORM\Table(name="section")
 */
class Section
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $description;
    /**
     * @ORM\ManyToOne(targetEntity="Area")
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id", nullable=false)
     * @var Section
     */
    protected $area;

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
     * @return integer
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param integer $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }
}