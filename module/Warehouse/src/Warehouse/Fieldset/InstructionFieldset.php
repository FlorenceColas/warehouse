<?php
/**
 * User: FlorenceColas
 * Date: 20/02/16
 * Version: 1.00
 * InstructionFieldset: Fieldset which contains the recipe instructions form fields
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Fieldset;

use Warehouse\Entity\Instruction;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class InstructionFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('instructions');

        $this->setHydrator(new DoctrineHydrator($objectManager,'Warehouse\Entity\Instruction',false));
        $this->setObject(new Instruction());

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Number',
            'name' => 'sequence',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'min' => '1',
                'max' => '100',
                'step' => '1', // default step interval is 1
            )
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Textarea',
            'name'    => 'description',
            'options' => array(
                'label' => ''
            ),
            'attributes' => [
                'maxlength' => 255,
                'cols' => 110,
                'rows' => 2,
//                'size' => 120,
            ]
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'recipe_id'
        ));

    }

    public function getInputFilterSpecification()
    {
        return array(
            'description' => array(
                'required' => false,
            ),
            'sequence' => array(
                'required' => false,
            ),
        );
    }
}