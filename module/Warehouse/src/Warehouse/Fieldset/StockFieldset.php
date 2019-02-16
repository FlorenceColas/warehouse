<?php

namespace Warehouse\Fieldset;

use Doctrine\Common\Persistence\ObjectManager;
use Warehouse\Entity\Stock;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Hydrator\ClassMethods as ClassMethodsHydrator;
use Zend\InputFilter\InputFilterProviderInterface;

class StockFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('stock');

        $this->objectManager = $objectManager;

        $this->setHydrator(new ClassMethodsHydrator(false));
        $this->setObject(new Stock());

        $this->setLabel('Stock');

        $this->init();
    }

    public function init(){
        $this->add([
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
        ]);

        $this->add([
            'attributes' => [
                'id' => 'barcode',
            ],
            'filters'    => [
                [
                    'name' => 'Zend\Filter\StringTrim'
                ],
            ],
            'name'       => 'barcode',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'attributes' => [
                'cols'      => 90,
                'maxlength' => 255,
                'rows'      => 1,
            ],
            'name'       => 'description',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Textarea',
        ]);

        $this->add([
            'attributes' => [
                'id' => 'chkautobarcode',

            ],
            'name'       => 'chkautobarcode',
            'options'    => [
                'checked_value'   => 'auto',
                'label'           => '',
                'unchecked_value' => 'manual',
            ],
            'type'       => 'Zend\Form\Element\Checkbox',
        ]);

        $this->add([
            'attributes' => [
                'id' => 'prefered',
            ],
            'name'       => 'prefered',
            'options'    => [
                'checked_value'   => '1',
                'label'           => '',
                'unchecked_value' => '99',
            ],
            'type'       => 'Zend\Form\Element\Checkbox',
        ]);

        $this->add([
            'attributes' => [
                'max'  => '999999',
                'min'  => '0',
                'step' => '0.5',
            ],
            'name'       => 'quantity',
            'options'    => [
                'label' => ''
            ],
            'type'       => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'attributes' => [
                'max'  => '999999',
                'min'  => '0',
                'step' => '0.5',
            ],
            'name'       => 'netquantity',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'attributes' => [
                'max'  => '999999',
                'min'  => '0',
                'step' => '0.5',
            ],
            'name'       => 'infothreshold',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'attributes' => [
                'max'  => '999999',
                'min'  => '0',
                'step' => '0.5',
            ],
            'name'       => 'criticalthreshold',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'name'    => 'status',
            'options' => [
                'attributes'    => [
                    'value' => \Warehouse\Controller\InventoryController::ENABLED,
                ],
                'label'         => '',
                'value_options' => [
                    \Warehouse\Controller\InventoryController::DISABLED => 'Disabled',
                    \Warehouse\Controller\InventoryController::ENABLED  => 'Enabled',
                ],
            ],
            'type'    => 'Zend\Form\Element\Select',
        ]);

        $this->add([
            'attributes' => [
                'cols'      => 50,
                'maxlength' => 50,
                'rows'      => 1,
            ],
            'name'       => 'notes',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Textarea',
        ]);

        $this->add([
            'name'    => 'stockmergement_id',
            'options' => [
                'empty_option' => 'Please choose the merger stock',
                'label'        => '',
            ],
            'type'    => 'Zend\Form\Element\Select',
        ]);

        $this->add([
            'name'    => 'supplierreference',
            'options' => [
                'label' => '',
            ],
            'type'    => 'Zend\Form\Element\Text',
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'barcode' => [
                'required' => true,
            ],
            'chkautobarcode' => [
                'required' => false,
            ],
            'criticalthreshold' => [
                'required' => true
            ],
            'description' => [
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                    [
                        'name' => 'StripTags',
                    ],
                ],
                'properties' => [
                    'required' => true,
                ],
                'required'   => true,
            ],
            'infothreshold' => [
                'required' => true,
            ],
            'stockmergement_id' => [
                'required' => false,
            ],
            'netquantity' => [
                'required' => true,
            ],
            'notes' => [
                'required' => false,
            ],
            'prefered' => [
                'required' => true,
            ],
            'quantity' => [
                'required' => true,
            ],
            'status' => [
                'required' => true,
            ],
        ];
    }
}
