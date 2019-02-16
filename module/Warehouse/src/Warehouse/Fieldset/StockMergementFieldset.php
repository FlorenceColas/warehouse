<?php

namespace Warehouse\Fieldset;

use Doctrine\Common\Persistence\ObjectManager;
use Warehouse\Entity\StockMergement;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Hydrator\ClassMethods as ClassMethodsHydrator;
use Zend\InputFilter\InputFilterProviderInterface;

class StockMergementFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('stockmergement');

        $this->objectManager = $objectManager;

        $this->setHydrator(new ClassMethodsHydrator(false));
        $this->setObject(new StockMergement());

        $this->setLabel('Stock Mergement');

        $this->init();
    }

    public function init(){
        $this->add([
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
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
                'max'  => '999999',
                'min'  => '0',
                'step' => '0.1',
            ],
            'name'       => 'netquantity',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'name'    => 'measureunit_id',
            'options' => [
                'empty_option' => 'Please choose the unit',
                'label'        => '',
            ],
            'type'    => 'Zend\Form\Element\Select',
        ]);

        $this->add([
            'name'    => 'section_id',
            'options' => [
                'empty_option' => 'Please choose the section',
                'label'        => '',
            ],
            'type'    => 'Zend\Form\Element\Select',
        ]);

        $this->add([
            'name'    => 'area_id',
            'options' => [
                'empty_option' => 'Please choose the area',
                'label'        => '',
            ],
            'type'    => 'Zend\Form\Element\Select',
        ]);

        $this->add([
            'name'    => 'supplier_id',
            'options' => [
                'empty_option' => 'Please choose the supplier',
                'label'        => '',
            ],
            'type'    => 'Zend\Form\Element\Select',
        ]);

        $this->add([
            'attributes' => [
                'max'  => '999999',
                'min'  => '0',
                'step' => '0.1',
            ],
            'name'       => 'eqtblsp',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'attributes' => [
                'max'  => '999999',
                'min'  => '0',
                'step' => '0.1',
            ],
            'name'       => 'eqcofsp',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'attributes' => [
                'max'  => '999999',
                'min'  => '0',
                'step' => '0.1',
            ],
            'name'       => 'eqteasp',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'attributes' => [
                'max'  => '999999',
                'min'  => '0',
                'step' => '0.1',
            ],
            'name'       => 'eqpinch',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Number',
        ]);

        $this->add([
            'attributes' => [
                'max'  => '999999',
                'min'  => '0',
                'step' => '0.1',
            ],
            'name'       => 'eqpiece',
            'options'    => [
                'label' => ''
            ],
            'type'       => 'Zend\Form\Element\Number',
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'area_id' => [
                'required' => true,
            ],
            'description' => [
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'properties' => [
                    'required' => true
                ],
                'required' => true,
            ],
            'eqcofsp' => [
                'required' => false,
            ],
            'eqpiece' => [
                'required' => false,
            ],
            'eqpinch' => [
                'required' => false,
            ],
            'eqtblsp' => [
                'required' => false,
            ],
            'eqteasp' => [
                'required' => false,
            ],
            'netquantity' => [
                'required' => false,
            ],
            'section_id' => [
                'required' => true,
            ],
            'supplier_id' => [
                'required' => true,
            ],
            'measureunit_id' => [
                'required' => true,
            ],
        ];
    }
}
