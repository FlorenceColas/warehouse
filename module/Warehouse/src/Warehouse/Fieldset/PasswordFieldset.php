<?php
/**
 * User: FlorenceColas
 * Date: 29/01/2017
 * Version: 1.00
 * UserFieldset: Fieldset which contains the user form
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Fieldset;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use Warehouse\Entity\User;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class PasswordFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('password');

        $this->setHydrator(new DoctrineHydrator($objectManager,'Warehouse\Entity\User',false));
        $this->setObject(new User());
        $this->setLabel('Password');

        $this->add(array(
            'type'    => 'Zend\Form\Element\Password',
            'name'    => 'password',
            'options' => array(
                'label' => 'Password: ',
            ),
            'attributes' => [
                'size'  => '30',
                'maxlength' => 50,
            ],
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Password',
            'name'    => 'newpassword1',
            'options' => array(
                'label' => 'New password: ',
            ),
            'attributes' => [
                'size'  => '30',
                'maxlength' => 50,
            ],
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Password',
            'name'    => 'newpassword2',
            'options' => array(
                'label' => 'New Password: ',
            ),
            'attributes' => [
                'size'  => '30',
                'maxlength' => 50,
            ],
        ));
    }

    /**
     * Define InputFilterSpecifications
     * @access public
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(
            'password' => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'min' => 1,
                            'max' => 50,
                        ),
                    ),
                ),
            ),
            'newpassword1' => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'min' => 8,
                            'max' => 50,
                        ),
                    ),
/*                    array(
                        'name' => 'Regex',
                        'options' => array(
                            'pattern' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*(.).*\1.*\1).{8,20}$/',
//                            'pattern' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#\$%\!])(?!.*(.).*\1.*\1).{8,20}$/',
                            'messages' => array(
                                \Zend\Validator\Regex::NOT_MATCH => "The password does not match: minimum 8 characters with minimum 1 digit, 1 uppercase and 1 lowercase",
                                \Zend\Validator\Regex::INVALID => "The password is invalid",
                            )
                        ),
                    ),*/
                ),
            ),
            'newpassword2' => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'min' => 8,
                            'max' => 50,
                        ),
                    ),
                    array(
                        'name' => 'Identical',
                        'options' => array(
                            'token' => 'newpassword1',
                        ),
                    ),
                    array(
                        'name' => 'Regex',
                        'options' => array(
                            'pattern' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*(.).*\1.*\1).{8,20}$/',
                            'messages' => array(
                                \Zend\Validator\Regex::NOT_MATCH => "The password does not match: minimum 8 characters with minimum 1 digit, 1 uppercase and 1 lowercase",
                                \Zend\Validator\Regex::INVALID => "The password is invalid",
                            )
                        ),
                    ),
                ),
            ),

        );
    }
}