<?php
/**
 * User: FlorenceColas
 * Date: 10/03/16
 * Version: 1.00
 * StockMergementForm: Stock Mergement Form
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Warehouse\Fieldset\StockMergementFieldset;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StockMergementForm extends Form implements ServiceLocatorAwareInterface
{
    protected $objectManager;
    protected $serviceLocator;

    public function init()
    {
        // Here, we have $this->serviceLocator !!
    }

    public function setServiceLocator(ServiceLocatorInterface $sl)
    {
        $this->serviceLocator = $sl;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    //public function __construct()
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('edit-stock-mergement-form');

        $this->objectManager = $objectManager;

        $this->setAttribute('method', 'post');
        $this->setHydrator(new DoctrineHydrator($this->objectManager,'Warehouse\Entity\StockMergement'));

        $stockFieldset = new StockMergementFieldset($objectManager);
        $stockFieldset->setUseAsBaseFieldset(true);
        $this->add($stockFieldset);

        $this->add(array(
            'name' => 'backToList',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Back to the list',
                'id'    => 'backToList'
            )
        ));
        $this->add(array(
            'name' => 'update',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Update',
                'id'    => 'update'
            )
        ));
        $this->add(array(
            'name' => 'create',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Create',
                'id'    => 'create'
            )
        ));
        $this->add(array(
            'name' => 'delete',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Delete',
                'id'    => 'delete'
            )
        ));
        $this->add(array(
            'name' => 'add',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'New Stock',
                'id'    => 'add'
            )
        ));
        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Cancel',
                'id'    => 'cancel'
            )
        ));
        $this->add(array(
            'name' => 'Export XLS',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Export XLS',
                'id'    => 'exportxls'
            )
        ));
    }
}