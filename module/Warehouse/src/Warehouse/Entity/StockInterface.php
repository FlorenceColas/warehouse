<?php
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
     * @ORM\ManyToOne(targetEntity="MeasureUnit")
     * @ORM\JoinColumn(name="measureunit_id", referencedColumnName="id", nullable=false)
     */
    protected $measureunit;
    /**
     * @ORM\ManyToOne(targetEntity="Stock")
     * @ORM\JoinColumn(name="stock_id", referencedColumnName="id", nullable=true)
     */
    protected $stock;
    /**
     * @ORM\ManyToOne(targetEntity="StockMergement")
     * @ORM\JoinColumn(name="stockmergement_id", referencedColumnName="id", nullable=false)
     */
    protected $stockmergement;
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
    public function getMeasureUnit()
    {
        return $this->measureunit;
    }

    /**
     * @param mixed $measureunit
     */
    public function setMeasureUnit($measureunit)
    {
        $this->measureunit = $measureunit;
    }

    /**
     * @return mixed
     */
    public function getStockMergement()
    {
        return $this->stockmergement;
    }

    /**
     * @param mixed $stockmergement
     */
    public function setStockMergement($stockmergement)
    {
        $this->stockmergement = $stockmergement;
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