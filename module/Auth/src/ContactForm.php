<?php

namespace Auth;

use Zend\Form\Form;

/**
 * Class ContactForm
 * @package Auth
 */
class ContactForm extends Form
{
    public function __construct()
    {
        parent::__construct('contact-form');

        $this->setAttribute('method', 'post');

        $this->add([
            'attributes' => [
                'autofocus'   => true,
                'maxlength'   => 100,
                'placeholder' => 'john.doe@mydomain.com',
                'size'        => '65',
            ],
            'name'       => 'email',
            'options'    => [
                'label' => 'Email: ',
            ],
            'type'       => 'Zend\Form\Element\Text',
        ]);

        $this->add([
            'attributes' => [
                'maxlength'   => 100,
                'placeholder' => 'John Doe',
                'size'        => '65',
            ],
            'name'       => 'name',
            'options'    => [
                'label' => 'Name: ',
            ],
            'type'       => 'Zend\Form\Element\Text',
        ]);

        $this->add([
            'attributes' => [
                'maxlength'   => 100,
                'placeholder' => 'Enter a subject',
                'size'        => '60',
            ],
            'name'    => 'title',
            'options' => [
                'label' => '',
            ],
            'type'    => 'Zend\Form\Element\Text',
        ]);

        $this->add([
            'attributes' => [
                'cols'        => 63,
                'maxlength'   => 500,
                'placeholder' => 'Enter your message',
                'rows'        => 12,
            ],
            'name'       => 'message',
            'options'    => [
                'label' => '',
            ],
            'type'       => 'Zend\Form\Element\Textarea',
        ]);

        $this->add([
            'attributes' => [
                'class' => 'btn-default-size',
                'id'    => 'back',
                'type'  => 'submit',
                'value' => 'Back to Login',
            ],
            'name'       => 'back',
        ]);

        $this->add([
            'attributes' => [
                'class' => 'btn-default-size',
                'id'    => 'send',
                'type'  => 'submit',
                'value' => 'Send',
            ],
            'name'       => 'send',
        ]);
    }
}
