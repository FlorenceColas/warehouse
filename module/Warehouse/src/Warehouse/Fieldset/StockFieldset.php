<?php
/**
 * User: FlorenceColas
 * Date: 20/02/16
 * Version: 1.00
 * StockFieldset: Fieldset which contains the stock form fields
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Fieldset;

use Warehouse\Entity\Stock;
use Warehouse\Enum\EnumStatus;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Element\Collection;
use Zend\Form\Element;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StockFieldset extends Fieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('stock');

        $this->objectManager = $objectManager;

        $this->setHydrator(new DoctrineHydrator($this->objectManager,'Warehouse\Entity\Stock',false));
        $this->setObject(new Stock());

        $this->setLabel('Stock');

        $this->init();
    }

    public function init(){
        $this->add([
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ]);

        $this->add(array(
            'type'    => 'Zend\Form\Element\Number',
            'name'    => 'barcode',
            'filters' => array(
                array('name' => 'Zend\Filter\StringTrim'),
            ),
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'id' => 'barcode',
                //'readonly' => TRUE,
            ),
        ));

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
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'chkautobarcode',
            'options' => array(
                'label' => '',
                'checked_value' => 'auto',
                'unchecked_value' => 'manual'
            ),
            'attributes' => array(
                'id' => 'chkautobarcode',

            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'prefered',
            'options' => array(
                'label' => '',
                'checked_value' => '1',
                'unchecked_value' => '99'
            ),
            'attributes' => array(
                'id' => 'prefered',

            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Number',
            'name' => 'quantity',
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
            'type' => 'Zend\Form\Element\Number',
            'name' => 'infothreshold',
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
            'type' => 'Zend\Form\Element\Number',
            'name' => 'criticalthreshold',
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
            'name' => 'status',
            'options' => array(
                'label' => '',
                'value_options' => array(
                    EnumStatus::Enabled => 'Enabled',
                    EnumStatus::Disabled => 'Disabled',
                ),
                'attributes' => array(
                    'value'  => EnumStatus::Enabled,
                ),
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\TextArea',
            'name'    => 'notes',
            'options' => array(
                'label' => ''
            ),
            'attributes' => [
                'cols' => 50,
                'rows' => 1,
                'maxlength' => 50
            ]
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'merge_id',
            'options' => array(
                'label' => '',
                'empty_option' => 'Please choose the merger stock'
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'supplierreference',
            'options' => array(
                'label' => ''
            )
        ));
        $attachments = new Collection('attachments');
        $attachments->setLabel('Attachments')
            ->setOptions(array(
                'count'          => 1,
                'allow_add'      => true,
                'allow_remove'   => true,
                'should_create_template' => true,
                'template_placeholder' => '__attachments__',
                'target_element' => new AttachmentFieldset($this->objectManager),
            ));
        $this->add($attachments);
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
            'barcode' => array(
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
            'quantity' => [
                'required' => true,
            ],
            'merge_id' => [
                'required' => false,
            ],
            'netquantity' => [
                'required' => true,
            ],
            'infothreshold' => [
                'required' => true,
            ],
            'criticalthreshold' => [
                'required' => true
            ],
            'status' => [
                'required' => true,
            ],
            'notes' => [
                'required' => false, //force none requirement to avoid some wrong message (is empty)
            ],
            'chkautobarcode' => [
                'required' => false,
            ],
            'prefered' => [
                'required' => true,
            ]
        );
    }
}