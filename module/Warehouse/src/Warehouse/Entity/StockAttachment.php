<?php

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\InventoryAttachmentRepository")
 * @ORM\Table(name="stock_attachment")
 */
class StockAttachment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="Stock", inversedBy="stockattachments")
     * @ORM\JoinColumn(name="stock_id", referencedColumnName="id", nullable=true)
     */
    private $stock_id;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="Stock", inversedBy="stockattachments")
     * @ORM\JoinColumn(name="attachement_id", referencedColumnName="id", nullable=true)
     */
    private $attachment_id;

    /**
     * @return $stock_id
     */
    public function getStockId()
    {
        return $this->stock_id;
    }

    /**
     * @param integer $stock_id
     */
    public function setRecipeId($stock_id)
    {
        $this->stock_id = $stock_id;
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
