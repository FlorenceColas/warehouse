<?php
namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\StockMergementRepository")
 * @ORM\Table(name="stockmergement")
 */
class StockMergement
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Area")
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id", nullable=false)
     */
    protected $area;
    /**
     * @ORM\ManyToOne(targetEntity="Section")
     * @ORM\JoinColumn(name="section_id", referencedColumnName="id", nullable=false)
     */
    protected $section;
    /**
     * @ORM\ManyToOne(targetEntity="Supplier")
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id",nullable=false)
     */
    protected $supplier;
    /**
     * @ORM\ManyToOne(targetEntity="MeasureUnit")
     * @ORM\JoinColumn(name="measureunit_id", referencedColumnName="id", nullable=false)
     */
    protected $measureunit;
    /**
     * @ORM\Column(type="string", length=50, nullable = false)
     */
    protected $description;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = true)
     */
    protected $eqcofsp;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = true)
     */
    protected $eqpiece;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = true)
     */
    protected $eqpinch;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = true)
     */
    protected $eqtblsp;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = true)
     */
    protected $eqteasp;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = false)
     */
    protected $netquantity;

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
     * @return int
     */
    public function getNetquantity()
    {
        return $this->netquantity;
    }

    /**
     * @param int $netquantity
     */
    public function setNetquantity($netquantity)
    {
        $this->netquantity = $netquantity;
    }

    /**
     * @return MeasureUnit
     */
    public function getMeasureUnit()
    {
        return $this->measureunit;
    }

    /**
     * @param mixed $unit
     */
    public function setMeasureUnit($measureunit)
    {
        $this->measureunit = $measureunit;
    }

    /**
     * @return mixed
     */
    public function getEqtblsp()
    {
        return $this->eqtblsp;
    }

    /**
     * @param mixed $eqtblsp
     */
    public function setEqtblsp($eqtblsp)
    {
        $this->eqtblsp = $eqtblsp;
    }

    /**
     * @return mixed
     */
    public function getEqcofsp()
    {
        return $this->eqcofsp;
    }

    /**
     * @param mixed $eqcofsp
     */
    public function setEqcofsp($eqcofsp)
    {
        $this->eqcofsp = $eqcofsp;
    }

    /**
     * @return mixed
     */
    public function getEqteasp()
    {
        return $this->eqteasp;
    }

    /**
     * @param mixed $eqteasp
     */
    public function setEqteasp($eqteasp)
    {
        $this->eqteasp = $eqteasp;
    }

    /**
     * @return mixed
     */
    public function getEqpinch()
    {
        return $this->eqpinch;
    }

    /**
     * @param mixed $eqpinch
     */
    public function setEqpinch($eqpinch)
    {
        $this->eqpinch = $eqpinch;
    }

    /**
     * @return mixed
     */
    public function getEqpiece()
    {
        return $this->eqpiece;
    }

    /**
     * @param mixed $eqpiece
     */
    public function setEqpiece($eqpiece)
    {
        $this->eqpiece = $eqpiece;
    }

    /**
     * @return Section
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param Section $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * @return Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param Area $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param Supplier $supplier
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
    }
}
