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

class UserFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('user');

        $this->setHydrator(new DoctrineHydrator($objectManager,'Warehouse\Entity\User',false));
        $this->setObject(new User());
        $this->setLabel('User');

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'logonName',
            'options' => array(
                'label' => 'Id: ',
            ),
            'attributes' => [
                'size'  => '50',
                'maxlength' => 50,
                'autofocus' => true,
            ],
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Password',
            'name'    => 'password',
            'options' => array(
                'label' => 'Password: ',
            ),
            'attributes' => [
                'size'  => '50',
                'maxlength' => 50,
            ],
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Datetime',
            'name'    => 'lastconnection',
            'options' => array(
                'label' => 'Last connection: ',
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'status',
            'options' => array(
                'label' => 'Status: '
            ),
            'attributes' => [
                'size' => 1,
                'maxlength' => 1
            ]
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'access',
            'options' => array(
                'label' => 'Access: '
            ),
            'attributes' => [
                'size' => 1,
                'maxlength' => 1
            ]
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'name',
            'options' => array(
                'label' => 'Name: ',
            ),
            'attributes' => [
                'size'  => '100',
                'maxlength' => 100,
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
            'logonName' => array(
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

            'lastconnection' => array(
                'required' => false,
            ),

            'status' => array(
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
                            'max' => 1,
                        ),
                    ),
                ),
            ),
            'access' => array(
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
                            'max' => 1,
                        ),
                    ),
                ),
            ),

            'name' => array(
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
                            'max' => 100,
                        ),
                    ),
                ),
            ),

        );
    }
}