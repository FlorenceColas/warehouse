<?php
/**
 * User: FlorenceColas
 * Date: 22/01/2017
 * Version: 1.00
 * StockInterface: Entity corresponding to stock interface table
 * Properties:
 *      - id
 *      - description
 *      - stockmergement (merge_id)
 *      - stock (stock_id)
 *      - quantity
 *      - unit (unit_id)
 *      - sens
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 */

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\StockInterfaceRepository")
 * @ORM\Table(name="stockinterface")
 */
class StockInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=50, nullable = false)
     */
    protected $description;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = false)
     */
    protected $quantity;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = false)
     */
    protected $quantitytointegrate;
    /**
     * @ORM\Column(type="integer", nullable = false)
     */
    protected $sens;
    /**
     * @ORM\ManyToOne(targetEntity="MeasureUnit")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=false)
     */
    protected $unit;
    /**
     * @ORM\ManyToOne(targetEntity="MeasureUnit")
     * @ORM\JoinColumn(name="unittointegrate_id", referencedColumnName="id", nullable=false)
     */
    protected $unittointegrate;
    /**
     * @ORM\ManyToOne(targetEntity="StockMergement")
     * @ORM\JoinColumn(name="merge_id", referencedColumnName="id", nullable=false)
     */
    protected $merge;
    /**
     * @ORM\ManyToOne(targetEntity="Stock")
     * @ORM\JoinColumn(name="stock_id", referencedColumnName="id", nullable=true)
     */
    protected $stock;

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
    public function getSens()
    {
        return $this->sens;
    }

    /**
     * @param mixed $sens
     */
    public function setSens($sens)
    {
        $this->sens = $sens;
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
    public function getMerge()
    {
        return $this->merge;
    }

    /**
     * @param mixed $merge
     */
    public function setMerge($merge)
    {
        $this->merge = $merge;
    }

    /**
     * @return mixed
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param mixed $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    /**
     * @return mixed
     */
    public function getQuantitytointegrate()
    {
        return $this->quantitytointegrate;
    }

    /**
     * @param mixed $quantitytointegrate
     */
    public function setQuantitytointegrate($quantitytointegrate)
    {
        $this->quantitytointegrate = $quantitytointegrate;
    }

    /**
     * @return mixed
     */
    public function getUnittointegrate()
    {
        return $this->unittointegrate;
    }

    /**
     * @param mixed $unittointegrate
     */
    public function setUnittointegrate($unittointegrate)
    {
        $this->unittointegrate = $unittointegrate;
    }



}