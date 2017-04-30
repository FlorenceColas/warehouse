<?php
/**
 * User: FlorenceColas
 * Date: 02/04/16
 * Version: 1.00
 * ShoppingList: Entity corresponding to shoppinglist table
 * Properties:
 *      - id
 *      - stock (stock_id)
 *      - description
 *      - quantity
 *      - unit (unit_id)
 *      - section (section_id)
 *      - supplier (supplier_id)
 *      - priority
 *      - area (area_id)
 *      - status
 *      - sendtostock
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\ShoppingListRepository")
 * @ORM\Table(name="shoppinglist")
 */
class ShoppingList
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="StockMergement")
     * @ORM\JoinColumn(name="merge_id", referencedColumnName="id", nullable=false)
     */
    protected $stockmergement;
    /**
     * @ORM\Column(type="string")
     */
    protected $description;
    /**
     * @ORM\Column(type="integer")
     */
    protected $quantity;
    /**
     * @ORM\ManyToOne(targetEntity="MeasureUnit")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=false)
     */
    protected $unit;
    /**
     * @ORM\ManyToOne(targetEntity="Section")
     * @ORM\JoinColumn(name="section_id", referencedColumnName="id", nullable=false)
     */
    protected $section;
    /**
     * @ORM\ManyToOne(targetEntity="Supplier")
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id", nullable=false)
     */
    protected $supplier;
    /**
     * @ORM\Column(type="integer")
     */
    protected $priority;
    /**
     * @ORM\ManyToOne(targetEntity="Area")
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id", nullable=false)
     */
    protected $area;
    /**
     * @ORM\Column(type="integer")
     */
    protected $status;
    /**
     * @ORM\ManyToOne(targetEntity="Recipe")
     * @ORM\JoinColumn(name="recipe_id", referencedColumnName="id", nullable=true)
     */
    protected $recipe;
    /**
     * @ORM\Column(type="integer")
     */
    protected $sendtostock;

    /**
     * @return mixed
     */
    public function getRecipe()
    {
        return $this->recipe;
    }

    /**
     * @param mixed $recipe
     */
    public function setRecipe($recipe)
    {
        $this->recipe = $recipe;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param mixed $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return mixed
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param mixed $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * @return mixed
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param mixed $supplier
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param mixed $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return mixed
     */
    public function getStockmergement()
    {
        return $this->stockmergement;
    }

    /**
     * @param mixed $stockmergement
     */
    public function setStockmergement($stockmergement)
    {
        $this->stockmergement = $stockmergement;
    }

    /**
     * @return mixed
     */
    public function getSendtostock()
    {
        return $this->sendtostock;
    }

    /**
     * @param mixed $sendtostock
     */
    public function setSendtostock($sendtostock)
    {
        $this->sendtostock = $sendtostock;
    }


}