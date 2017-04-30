<?php
/**
 * User: FlorenceColas
 * Date: 10/01/2017
 * Version: 1.00
 * StockMovement: Entity corresponding to stock movement table
 * Properties:
 *      - id
 *      - stock (stock_id)
 *      - quantity
 *      - movement
 *      - date
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 */

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\StockMovementRepository")
 * @ORM\Table(name="stockmovement")
 */class StockMovement
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="decimal", precision=3, scale=1, nullable=false)
     */
    protected $quantity;
    /**
     * @ORM\Column(type="integer", nullable = false)
     */
    protected $movement;
    /**
     * @ORM\Column(type="date", nullable = false)
     */
    protected $datemovement;
    /**
     * @ORM\ManyToOne(targetEntity="Stock", inversedBy="stockmovement")
     * @ORM\JoinColumn(name="stock_id", referencedColumnName="id", nullable=false)
     */
    private $stock;

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
    public function getMovement()
    {
        return $this->movement;
    }

    /**
     * @param mixed $movement
     */
    public function setMovement($movement)
    {
        $this->movement = $movement;
    }

    /**
     * @return mixed
     */
    public function getDatemovement()
    {
        return $this->datemovement;
    }

    /**
     * @param mixed $datemovement
     */
    public function setDatemovement($datemovement)
    {
        $this->datemovement = $datemovement;
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


}