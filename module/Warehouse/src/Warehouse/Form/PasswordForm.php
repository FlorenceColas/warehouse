<?php
/**
 * User: FlorenceColas
 * Date: 29/01/2017
 * Version: 1.00
 * UserForm: user Form
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Warehouse\Fieldset\PasswordFieldset;

class PasswordForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('password-form');

        $this->setAttribute('method', 'post');
        $this->setHydrator(new DoctrineHydrator($objectManager,'Warehouse\Entity\User'));

        $passwordFieldset = new PasswordFieldset($objectManager);
        $passwordFieldset->setUseAsBaseFieldset(true);
        $this->add($passwordFieldset);

        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'class' => 'btn-default-size',
                'type'  => 'submit',
                'value' => 'Cancel',
                'id'    => 'cancel'
            )
        ));

        $this->add(array(
            'name' => 'update',
            'attributes' => array(
                'class' => 'btn-default-size',
                'type'  => 'submit',
                'value' => 'Update',
                'id'    => 'update'
            )
        ));

    }
}