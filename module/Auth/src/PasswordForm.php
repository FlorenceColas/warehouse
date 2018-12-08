<?php

namespace Auth;

use Zend\Form\Form;

class PasswordForm extends Form
{
    public function __construct()
    {
        parent::__construct('password-form');

        $this->setAttribute('method', 'post');

        $passwordFieldset = new PasswordFieldset();
        $passwordFieldset->setUseAsBaseFieldset(true);
        $this->add($passwordFieldset);

        $this->add([
            'attributes' => [
                'class' => 'btn-default-size',
                'id'    => 'cancel',
                'type'  => 'submit',
                'value' => 'Cancel',
            ],
            'name'       => 'cancel',
        ]);

        $this->add([
            'attributes' => [
                'class' => 'btn-default-size',
                'id'    => 'update',
                'type'  => 'submit',
                'value' => 'Update',
            ],
            'name'       => 'update',
        ]);
    }
}
