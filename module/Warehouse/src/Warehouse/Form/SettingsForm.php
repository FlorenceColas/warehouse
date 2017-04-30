<?php
/**
 * User: FlorenceColas
 * Date: 01/03/16
 * Version: 1.00
 * SettingsForm: Settings Tables Form
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class SettingsForm extends Form
{
    public function __construct(ObjectManager $objectManager, $entity)
    {
        parent::__construct('settings-form');

        $this->setAttribute('method', 'post');
        $this->setHydrator(new DoctrineHydrator($objectManager,'Warehouse\Entity\\'.$entity));

        $this->add([
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ]);

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'description',
            'options' => array(
                'label' => ''
            )
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'abbreviation',
            'options' => array(
                'label' => ''
            )
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Hidden',
            'name'    => 'useinstock',
            'options' => array(
                'label' => ''
            )
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'unit',
            'options' => array(
                'label' => ''
            )
        ));

        $this->add(array(
            'name' => 'update',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Update',
                'id'    => 'update'
            )
        ));

        $this->add(array(
            'name' => 'cancel',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Cancel',
                'id'    => 'cancel'
            )
        ));

    }
}