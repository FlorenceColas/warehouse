<?php
/**
 * User: FlorenceColas
 * Date: 22/01/2017
 * Version: 1.00
 * StockMergement: Entity corresponding to stock mergement table
 * Properties:
 *      - id
 *      - description
 *      - netquantity
 *      - unit (measureunit_id)
 *      - eqtblsp
 *      - eqcofsp
 *      - eqteasp
 *      - eqpinch
 *      - eqpiece
 *      - section
 *      - area
 *      - supplier
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 */

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
     * @ORM\Column(type="string", length=50, nullable = false)
     */
    protected $description;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = false)
     */
    protected $netquantity;
    /**
     * @ORM\ManyToOne(targetEntity="MeasureUnit")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id", nullable=false)
     */
    protected $unit;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = true)
     */
    protected $eqtblsp;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = true)
     */
    protected $eqcofsp;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = true)
     */
    protected $eqteasp;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = true)
     */
    protected $eqpinch;
    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable = true)
     */
    protected $eqpiece;
    /**
     * @ORM\ManyToOne(targetEntity="Section")
     * @ORM\JoinColumn(name="section_id", referencedColumnName="id", nullable=false)
     * @var Section
     */
    protected $section;
    /**
     * @ORM\ManyToOne(targetEntity="Area")
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id", nullable=false)
     * @var Area
     */
    protected $area;
    /**
     * @ORM\ManyToOne(targetEntity="Supplier")
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id",nullable=false)
     * @var Supplier
     */
    protected $supplier;

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