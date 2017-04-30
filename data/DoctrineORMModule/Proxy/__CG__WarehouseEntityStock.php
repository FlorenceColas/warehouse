<?php

namespace DoctrineORMModule\Proxy\__CG__\Warehouse\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Stock extends \Warehouse\Entity\Stock implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = [];



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return ['__isInitialized__', 'id', 'barcode', 'description', 'quantity', 'infothreshold', 'criticalthreshold', 'supplierreference', 'status', 'attachments', 'stockmovement', 'notes', 'merge', 'netquantity', 'prefered'];
        }

        return ['__isInitialized__', 'id', 'barcode', 'description', 'quantity', 'infothreshold', 'criticalthreshold', 'supplierreference', 'status', 'attachments', 'stockmovement', 'notes', 'merge', 'netquantity', 'prefered'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Stock $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', []);

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function getBarcode()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBarcode', []);

        return parent::getBarcode();
    }

    /**
     * {@inheritDoc}
     */
    public function setBarcode($barcode)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setBarcode', [$barcode]);

        return parent::setBarcode($barcode);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDescription', []);

        return parent::getDescription();
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDescription', [$description]);

        return parent::setDescription($description);
    }

    /**
     * {@inheritDoc}
     */
    public function getQuantity()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getQuantity', []);

        return parent::getQuantity();
    }

    /**
     * {@inheritDoc}
     */
    public function setQuantity($quantity)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setQuantity', [$quantity]);

        return parent::setQuantity($quantity);
    }

    /**
     * {@inheritDoc}
     */
    public function getInfothreshold()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getInfothreshold', []);

        return parent::getInfothreshold();
    }

    /**
     * {@inheritDoc}
     */
    public function setInfothreshold($infothreshold)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setInfothreshold', [$infothreshold]);

        return parent::setInfothreshold($infothreshold);
    }

    /**
     * {@inheritDoc}
     */
    public function getCriticalthreshold()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCriticalthreshold', []);

        return parent::getCriticalthreshold();
    }

    /**
     * {@inheritDoc}
     */
    public function setCriticalthreshold($criticalthreshold)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCriticalthreshold', [$criticalthreshold]);

        return parent::setCriticalthreshold($criticalthreshold);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStatus', []);

        return parent::getStatus();
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($status)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setStatus', [$status]);

        return parent::setStatus($status);
    }

    /**
     * {@inheritDoc}
     */
    public function getSupplierreference()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSupplierreference', []);

        return parent::getSupplierreference();
    }

    /**
     * {@inheritDoc}
     */
    public function setSupplierreference($supplierreference)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSupplierreference', [$supplierreference]);

        return parent::setSupplierreference($supplierreference);
    }

    /**
     * {@inheritDoc}
     */
    public function addAttachment(\Warehouse\Entity\Attachment $attachment)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addAttachment', [$attachment]);

        return parent::addAttachment($attachment);
    }

    /**
     * {@inheritDoc}
     */
    public function removeAttachment(\Warehouse\Entity\Attachment $attachment)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeAttachment', [$attachment]);

        return parent::removeAttachment($attachment);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttachment()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAttachment', []);

        return parent::getAttachment();
    }

    /**
     * {@inheritDoc}
     */
    public function addStockMovement(\Warehouse\Entity\StockMovement $stockmovement)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addStockMovement', [$stockmovement]);

        return parent::addStockMovement($stockmovement);
    }

    /**
     * {@inheritDoc}
     */
    public function removeStockMovement(\Warehouse\Entity\StockMovement $stockmovement)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeStockMovement', [$stockmovement]);

        return parent::removeStockMovement($stockmovement);
    }

    /**
     * {@inheritDoc}
     */
    public function getStockMovement()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStockMovement', []);

        return parent::getStockMovement();
    }

    /**
     * {@inheritDoc}
     */
    public function getNetquantity()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getNetquantity', []);

        return parent::getNetquantity();
    }

    /**
     * {@inheritDoc}
     */
    public function setNetquantity($netquantity)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setNetquantity', [$netquantity]);

        return parent::setNetquantity($netquantity);
    }

    /**
     * {@inheritDoc}
     */
    public function getNotes()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getNotes', []);

        return parent::getNotes();
    }

    /**
     * {@inheritDoc}
     */
    public function setNotes($notes)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setNotes', [$notes]);

        return parent::setNotes($notes);
    }

    /**
     * {@inheritDoc}
     */
    public function getMerge()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getMerge', []);

        return parent::getMerge();
    }

    /**
     * {@inheritDoc}
     */
    public function setMerge($merge)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setMerge', [$merge]);

        return parent::setMerge($merge);
    }

    /**
     * {@inheritDoc}
     */
    public function getPrefered()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPrefered', []);

        return parent::getPrefered();
    }

    /**
     * {@inheritDoc}
     */
    public function setPrefered($prefered)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPrefered', [$prefered]);

        return parent::setPrefered($prefered);
    }

}
