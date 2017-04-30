<?php
/**
 * User: FlorenceColas
 * Date: 20/02/16
 * Version: 1.00
 * StockMergementFieldset: Fieldset which contains the stock mergement form fields
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Fieldset;

use Warehouse\Entity\StockMergement;
use Warehouse\Enum\EnumStatus;
use Warehouse\Enum\EnumTableSettings;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Element\Collection;
use Zend\Form\Element;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StockMergementFieldset extends Fieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('stockmergement');

        $this->objectManager = $objectManager;

        $this->setHydrator(new DoctrineHydrator($this->objectManager,'Warehouse\Entity\StockMergement',false));
        $this->setObject(new StockMergement());

        $this->setLabel('Stock Mergement');

        $this->init();
    }

    public function init(){
        $this->add([
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ]);

        $this->add(array(
            'type'    => 'Zend\Form\Element\TextArea',
            'name'    => 'description',
            'options' => array(
                'label' => ''
            ),
            'attributes' => [
                'cols' => 90,
                'rows' => 1,
                'maxlength' => 255
            ]
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Number',
            'name' => 'netquantity',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'min' => '0',
                'max' => '100000',
                'step' => '1', // default step interval is 1
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'unit_id',
            'options' => array(
                'label' => '',
                'empty_option' => 'Please choose the unit'
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'section_id',
            'options' => array(
                'label' => '',
                'empty_option' => 'Please choose the section'
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'area_id',
            'options' => array(
                'label' => '',
                'empty_option' => 'Please choose the area',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'supplier_id',
            'options' => array(
                'label' => '',
                'empty_option' => 'Please choose the supplier'
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Number',
            'name' => 'eqtblsp',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'min' => '0',
                'max' => '1000',
                'step' => '1', // default step interval is 1
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Number',
            'name' => 'eqcofsp',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'min' => '0',
                'max' => '1000',
                'step' => '1', // default step interval is 1
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Number',
            'name' => 'eqteasp',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'min' => '0',
                'max' => '1000',
                'step' => '1', // default step interval is 1
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Number',
            'name' => 'eqpinch',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'min' => '0',
                'max' => '1000',
                'step' => '1', // default step interval is 1
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Number',
            'name' => 'eqpiece',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'min' => '0',
                'max' => '1000',
                'step' => '1', // default step interval is 1
            )
        ));

    }

    public function setServiceLocator(ServiceLocatorInterface $sl)
    {
        $this->serviceLocator = $sl;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function getInputFilterSpecification()
    {
        return array(
            'unit_id' => array(
                'required' => true,
            ),
            'description' => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
                'properties' => array(
                    'required' => true
                )
            ),
            'netquantity' => [
                'required' => false,
            ],
            'section_id' => [
                'required' => true,
            ],
            'supplier_id' => [
                'required' => true,
            ],
            'eqtblsp' => [
                'required' => false,
            ],
            'eqcofsp' => [
                'required' => false,
            ],
            'eqteasp' => [
                'required' => false,
            ],
            'eqpinch' => [
                'required' => false,
            ],
            'eqpiece' => [
                'required' => false,
            ],
        );
    }
}