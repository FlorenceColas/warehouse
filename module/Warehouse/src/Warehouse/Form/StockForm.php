<?php

namespace Warehouse\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Warehouse\Fieldset\StockFieldset;
use Zend\Form\Form;

class StockForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('edit-stock-form');

        $this->setAttribute('method', 'post');
        $this->setHydrator(new DoctrineHydrator($objectManager,'Warehouse\Entity\Stock'));

        $stockFieldset = new StockFieldset($objectManager);
        $stockFieldset->setUseAsBaseFieldset(true);
        $this->add($stockFieldset);

        $this->add([
            'attributes' => [
                'id'    => 'backToList',
                'type'  => 'submit',
                'value' => 'Back to the list',
            ],
            'name'       => 'backToList',
        ]);
        $this->add([
            'attributes' => [
                'id'    => 'update',
                'type'  => 'submit',
                'value' => 'Update',
            ],
            'name'       => 'update',
        ]);
        $this->add([
            'attributes' => [
                'id'    => 'create',
                'type'  => 'submit',
                'value' => 'Create',
            ],
            'name'       => 'create',
        ]);
        $this->add([
            'attributes' => [
                'id'    => 'delete',
                'type'  => 'submit',
                'value' => 'Delete',
            ],
            'name'       => 'delete',
        ]);
        $this->add([
            'attributes' => [
                'id'    => 'add',
                'type'  => 'submit',
                'value' => 'New Article',
            ],
            'name'       => 'add',
        ]);
        $this->add([
            'attributes' => [
                'id'    => 'cancel',
                'type'  => 'submit',
                'value' => 'Cancel',
            ],
            'name'       => 'cancel',
        ]);
        $this->add([
            'attributes' => [
                'id'    => 'autobarcode',
                'type'  => 'submit',
                'value' => 'Auto',
            ],
            'name'       => 'autobarcode',
        ]);
        $this->add([
            'attributes' => [
                'id'    => 'exportxls',
                'type'  => 'submit',
                'value' => 'Export XLS',
           ],
            'name'       => 'Export XLS',
        ]);
    }
}
