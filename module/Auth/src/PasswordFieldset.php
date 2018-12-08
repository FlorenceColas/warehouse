<?php

namespace Auth;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class PasswordFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('password');

        $this->setLabel('Password');

        $this->add([
            'attributes' => [
                'maxlength' => 50,
                'size'      => '30',
            ],
            'name'       => 'password',
            'options'    => [
                'label' => 'Password: ',
            ],
            'type'       => 'Zend\Form\Element\Password',
        ]);

        $this->add([
            'attributes' => [
                'maxlength' => 50,
                'size'      => '30',
            ],
            'name'       => 'newpassword1',
            'options'    => [
                'label' => 'New password: ',
            ],
            'type'       => 'Zend\Form\Element\Password',
        ]);

        $this->add([
            'attributes' => [
                'maxlength' => 50,
                'size'      => '30',
            ],
            'name'       => 'newpassword2',
            'options'    => [
                'label' => 'New Password: ',
            ],
            'type'       => 'Zend\Form\Element\Password',
        ]);
    }

    /**
     * Define InputFilterSpecifications
     * @access public
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'password' => [
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags']
                ],
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'max' => 50,
                            'min' => 1,
                        ],
                    ],
                ],
            ],
            'newpassword1' => [
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags']
                ],
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 8,
                            'max' => 50,
                        ],
                    ],
                ],
            ],
            'newpassword2' => [
                'filters'    => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags']
                ],
                'required'    => true,
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'max' => 50,
                            'min' => 8,
                        ],
                    ],
                    [
                        'name'    => 'Identical',
                        'options' => [
                            'token' => 'newpassword1',
                        ],
                    ],
                    [
                        'name'    => 'Regex',
                        'options' => [
                            'messages' => [
                                \Zend\Validator\Regex::NOT_MATCH => "The password does not match: minimum 8 characters with minimum 1 digit, 1 uppercase and 1 lowercase",
                                \Zend\Validator\Regex::INVALID => "The password is invalid",
                            ],
                            'pattern' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*(.).*\1.*\1).{8,20}$/',
                        ],
                    ],
                ],
            ],
        ];
    }
}
