<?php
/**
 * User: FlorenceColas
 * Date: 07/02/16
 * Version: 1.00
 * CriteriaStockForm: Form which contains inventory criterias
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Form;

use Warehouse\Enum\EnumAvailability;
use Warehouse\Enum\EnumStatus;
use \Zend\Form\Form;
use Zend\InputFilter\InputFilterAwareInterface;

class CriteriaStockMergementForm extends Form implements InputFilterAwareInterface

{
    public function __construct($sectionValues, $areaValues)
    {
        parent::__construct('CriteriaStock');

        $this->setAttribute('method', 'post');

        //Field description
        $this->add(
            [
                'name' => 'description', //field name
                'type' => 'Zend\Form\Element\Text',   //field type
                'attributes' =>
                [
                    'id' => 'description',  //field id
                ],
                'options' =>
                [
//                    'label' => '',  //field label
                ],
            ]
        );

        //Field availability - select
        $this->add(
            [
                'name' => 'availability',
                'type' => 'Zend\Form\Element\Select',
                'value' => EnumAvailability::All,
                'options' => [
       //             'label' => '',
                    'value_options' => [
                        EnumAvailability::All => 'All',
                        EnumAvailability::OnStock => 'On stock',
                        EnumAvailability::NotOnStock => 'Not on stock',
                        EnumAvailability::UnderInfoThreshold => 'Under Info Threshold',
                        EnumAvailability::UnderCriticalThreshold => 'Under Critical Threshold',
                    ],
                ]
            ]
        );

        //Field area - select
        $this->add(
            [
                'name' => 'area',
                'type' => 'Zend\Form\Element\Select',
                'options' => [
                    'value_options' => $areaValues,
                ]
            ]
        );

        //Field status - radio button
        $this->add(
            [
                'name' => 'status',
                'type' => 'Zend\Form\Element\Radio',
                'attributes' =>
                    [
                        'id' => 'status',
                        'value' => EnumStatus::Enabled,
                    ],
                'options' =>
                    [
                       'value_options' =>
                            [
                                EnumStatus::Enabled => ' Enabled',
                                EnumStatus::Disabled => ' Disabled',
                            ],
                    ],
            ]
        );

        $this->add(
            [
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'section',
                'required' => false,
                'allow_empty' => 'true',
                'options' =>
                [
                    'value_options' => $sectionValues,
                ],
                'attributes' => [
                    //'value' => ['1','2']  //default value(s)
                ]
            ]
        );

        // Search button
        $this->add([
            'name' => 'search',         // button name
            'type' => 'Submit',         // button type
            'attributes' => [
                'value' => 'Search',    // button label
                'id' => 'search',       // button id
            ],
        ]);


    }
}
