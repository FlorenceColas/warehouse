<?php
/**
 * User: FlorenceColas
 * Date: 08/03/16
 * Version: 1.00
 * CriteriaRecipeForm: Form which contains recipes criterias
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterAwareInterface;

class CriteriaRecipeForm extends Form implements InputFilterAwareInterface
{
    public function __construct($categoryValues)
    {
        parent::__construct('CriteriaRecipe');

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

        $this->add(
            [
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'category',
                'required' => false,
                'allow_empty' => 'true',
                'options' =>
                    [
                        'value_options' => $categoryValues,
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