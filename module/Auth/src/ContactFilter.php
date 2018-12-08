<?php

namespace Auth;

use Zend\InputFilter\InputFilter;

/**
 * Class ContactFilter
 * @package Auth
 */
class ContactFilter extends InputFilter
{
    public function __construct()
    {
        $this->add([
            'filters' => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'name'       => 'email',
            'required'   => true,
            'validators' => [
                [
                    'name'    => 'EmailAddress',
                    'options' => [
                        'max' => 100,
                        'min' => 1,
                    ],
                ],
            ],
        ]);

        $this->add([
            'filters' => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'name'       => 'name',
            'required'   => true,
            'validators' => [
                [
                    'name'    => 'StringLength',
                    'options' => [
                        'max' => 100,
                        'min' => 1,
                    ],
                ],
            ],
        ]);

        $this->add([
            'filters'    => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'name'       => 'title',
            'required'   => true,
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'max' => 100,
                        'min' => 1,
                    ],
                ],
            ],
        ]);

        $this->add([
            'filters'    => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'name'       => 'message',
            'required'   => true,
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'max' => 500,
                        'min' => 1,
                    ],
                ],
            ],
        ]);
    }
}
