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

use Zend\Form\Form;
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
                'value' => \Warehouse\Controller\StockmergementController::ALL,
                'options' => [
       //             'label' => '',
                    'value_options' => [
                        \Warehouse\Controller\StockmergementController::ALL => 'All',
                        \Warehouse\Controller\StockmergementController::ON_STOCK => 'On stock',
                        \Warehouse\Controller\StockmergementController::NOT_ON_STOCK => 'Not on stock',
                        \Warehouse\Controller\StockmergementController::UNDER_INFO_THRESHOLD => 'Under Info Threshold',
                        \Warehouse\Controller\StockmergementController::UNDER_CRITICAL_THRESHOLD => 'Under Critical Threshold',
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
                        'value' => \Warehouse\Controller\InventoryController::ENABLED,
                    ],
                'options' =>
                    [
                       'value_options' =>
                            [
                                \Warehouse\Controller\InventoryController::ENABLED  => ' Enabled',
                                \Warehouse\Controller\InventoryController::DISABLED => ' Disabled',
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
