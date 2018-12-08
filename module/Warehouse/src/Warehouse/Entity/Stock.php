<?php
namespace Warehouse\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\StockRepository")
 * @ORM\Table(name="stock")
 */
class Stock
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToMany(targetEntity="Attachment")
     * @ORM\JoinTable(name="stock_attachment",
     *      joinColumns={@ORM\JoinColumn (name="stock_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="attachment_id", referencedColumnName="id")}
     *      )
     **/
    protected $attachments;
    /**
     * @ORM\ManyToOne(targetEntity="StockMergement")
     * @ORM\JoinColumn(name="stockmergement_id", referencedColumnName="id", nullable=true)
     */
    protected $stockmergement;
    /**
     * @ORM\OneToMany(targetEntity="StockMovement", mappedBy="stock", cascade={"persist","remove"})
     * @var ArrayCollection StockMovement[]
     **/
    protected $stockmovement;
    /**
     * @ORM\Column(type="decimal", precision=18, scale=0)
     */
    protected $barcode;
    /**
     * @ORM\Column(type="decimal", precision=8, scale=1)
     */
    protected $criticalthreshold;
    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $description;
    /**
     * @ORM\Column(type="decimal", precision=8, scale=1)
     */
    protected $infothreshold;
    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $notes;
    /**
     * @ORM\Column(type="decimal", precision=8, scale=1)
     */
    protected $quantity;
    /**
     * @ORM\Column(type="integer")
     */
    protected $status;
    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    protected $supplierreference;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $netquantity;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $prefered;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
        $this->stockmovement = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return decimal
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * @param decimal $barcode
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return decimal
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param decimal $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return decimal
     */
    public function getInfothreshold()
    {
        return $this->infothreshold;
    }

    /**
     * @param decimal $infothreshold
     */
    public function setInfothreshold($infothreshold)
    {
        $this->infothreshold = $infothreshold;
    }

    /**
     * @return decimal
     */
    public function getCriticalthreshold()
    {
        return $this->criticalthreshold;
    }

    /**
     * @param decimal $criticalthreshold
     */
    public function setCriticalthreshold($criticalthreshold)
    {
        $this->criticalthreshold = $criticalthreshold;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getSupplierreference()
    {
        return $this->supplierreference;
    }

    /**
     * @param string $supplierreference
     */
    public function setSupplierreference($supplierreference)
    {
        $this->supplierreference = $supplierreference;
    }

    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments[] = $attachment;
        $attachment->setStock($this);
        return $this;
    }

    /**
     * @param Attachment $attachment
     */
    public function removeAttachment(Attachment $attachment)
    {
        $this->attachments->removeElement($attachment);
    }

    /**
     * @return StockMovement[]
     */
    public function getAttachment()
    {
        return $this->attachments;
    }

    /**
     * @param StockMovement $stockmovement
     */
    public function addStockMovement(StockMovement $stockmovement)
    {
        $this->stockmovement[] = $stockmovement;
        $stockmovement->setStock($this);
        return $this;
    }

    /**
     * @param StockMovement $stockmovement
     */
    public function removeStockMovement(StockMovement $stockmovement)
    {
        $this->stockmovement->removeElement($stockmovement);
    }

    /**
     * @return StockMovement[]
     */
    public function getStockMovement()
    {
        return $this->stockmovement;
    }

    /**
     * @return mixed
     */
    public function getNetquantity()
    {
        return $this->netquantity;
    }

    /**
     * @param mixed $netquantity
     */
    public function setNetquantity($netquantity)
    {
        $this->netquantity = $netquantity;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
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
    public function getPrefered()
    {
        return $this->prefered;
    }

    /**
     * @param mixed $prefered
     */
    public function setPrefered($prefered)
    {
        $this->prefered = $prefered;
    }


}