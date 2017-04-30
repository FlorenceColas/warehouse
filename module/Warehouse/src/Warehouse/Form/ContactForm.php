<?php
/**
 * User: FlorenceColas
 * Date: 27/01/2017
 * Version: 1.00
 * ContactForm: Contact Form
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\Form\Form;

class ContactForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('contact-form');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'email',
            'options' => array(
                'label' => 'Email: ',
            ),
            'attributes' => [
                'size'  => '65',
                'maxlength' => 100,
                'placeholder' => 'john.doe@mydomain.com',
                'autofocus' => true,
            ],
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'name',
            'options' => array(
                'label' => 'Name: '
            ),
            'attributes' => [
                'size'  => '65',
                'maxlength' => 100,
                'placeholder' => 'John Doe',
            ],
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'title',
            'options' => array(
                'label' => ''
            ),
            'attributes' => [
                'size'  => '60',
                'maxlength' => 100,
                'placeholder' => 'Enter a subject',
            ],
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Textarea',
            'name'    => 'message',
            'options' => array(
                'label' => ''
            ),
            'attributes' => [
                'maxlength' => 500,
                'cols' => 63,
                'rows' => 12,
                'placeholder' => 'Enter your message',
            ],
        ));

        $this->add(array(
            'name' => 'back',
            'attributes' => array(
                'class' => 'btn-default-size',
                'type'  => 'submit',
                'value' => 'Back to Login',
                'id'    => 'back'
            )
        ));

        $this->add(array(
            'name' => 'send',
            'attributes' => array(
                'class' => 'btn-default-size',
                'type'  => 'submit',
                'value' => 'Send',
                'id'    => 'send'
            )
        ));
    }
}