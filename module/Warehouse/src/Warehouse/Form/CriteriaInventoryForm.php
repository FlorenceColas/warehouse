<?php

namespace Warehouse\Form;

use \Zend\Form\Form;
use Zend\InputFilter\InputFilterAwareInterface;

class CriteriaInventoryForm extends Form implements InputFilterAwareInterface
{
    public function __construct($sectionValues, $areaValues)
    {
        parent::__construct('CriteriaInventory');

        $this->setAttribute('method', 'post');

        $this->add([
            'attributes' => [
                'id' => 'description',
            ],
            'name'       => 'description',
            'type'       => 'Zend\Form\Element\Text',
        ]);

        $this->add([
            'name'    => 'availability',
            'options' => [
                'value_options' => [
                    \Warehouse\Controller\StockmergementController::ALL                      => 'All',
                    \Warehouse\Controller\StockmergementController::ON_STOCK                 => 'On stock',
                    \Warehouse\Controller\StockmergementController::NOT_ON_STOCK             => 'Not on stock',
                    \Warehouse\Controller\StockmergementController::UNDER_INFO_THRESHOLD     => 'Under Info Threshold',
                    \Warehouse\Controller\StockmergementController::UNDER_CRITICAL_THRESHOLD => 'Under Critical Threshold',
                ],
            ],
            'type'   => 'Zend\Form\Element\Select',
            'value'  => \Warehouse\Controller\StockmergementController::ALL,
        ]);

        $this->add([
            'name'    => 'area',
            'options' => [
                'value_options' => $areaValues,
            ],
            'type'    => 'Zend\Form\Element\Select',
        ]);

        $this->add([
            'attributes' => [
                'id'    => 'status',
                'value' => \Warehouse\Controller\InventoryController::ENABLED,
            ],
            'name'       => 'status',
            'options'    => [
               'value_options' => [
                   \Warehouse\Controller\InventoryController::ENABLED  => ' Enabled',
                   \Warehouse\Controller\InventoryController::DISABLED => ' Disabled',
                ]
            ],
            'type'       => 'Zend\Form\Element\Radio',
        ]);

        $this->add([
            'allow_empty' => 'true',
            'name'        => 'section',
            'options'     => [
                'value_options' => $sectionValues,
            ],
            'required'    => false,
            'type'        => 'Zend\Form\Element\MultiCheckbox',
        ]);

        $this->add([
            'name'       => 'search',
            'type'       => 'Submit',
            'attributes' => [
                'id'    => 'search',
                'value' => 'Search',
            ],
        ]);
    }
}
