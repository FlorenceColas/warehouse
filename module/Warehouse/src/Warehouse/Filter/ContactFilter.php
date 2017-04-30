<?php
/**
 * User: FlorenceColas
 * Date: 28/01/2017
 * Version: 1.00
 * ContactFilter: contains filter and validator for the contact form
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Filter;

use Zend\InputFilter\InputFilter;

class ContactFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'    => 'email',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'min' => 1,
                        'max' => 100,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name'    => 'name',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 1,
                        'max' => 100,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name'    => 'title',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 1,
                        'max' => 100,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name'    => 'message',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 1,
                        'max' => 500,
                    ),
                ),
            ),
        ));
    }
}