<?php
namespace Warehouse\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Warehouse\Fieldset\RecipeFieldset;
use Zend\Form\Form;
use Zend\Form\Element;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class RecipeForm extends Form
{
    protected $ingredientsField;

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('edit-recipe-form');

        $this->setAttribute('method', 'post');
        $this->setHydrator(new DoctrineHydrator($objectManager,'Warehouse\Entity\Recipe'));

        $recipeFieldset = new RecipeFieldset($objectManager);
        $recipeFieldset->setUseAsBaseFieldset(true);
        $this->add($recipeFieldset);

        $this->add(array(
            'name' => 'backToList',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Back to the list',
                'id'    => 'backToList'
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
            'name' => 'create',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Create',
                'id'    => 'create'
            )
        ));
        $this->add(array(
            'name' => 'delete',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Delete',
                'id'    => 'delete'
            )
        ));
        $this->add(array(
            'name' => 'check',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Check ingredients availability',
                'id'    => 'check'
            )
        ));
        $this->add(array(
            'name' => 'add',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'New recipe',
                'id'    => 'add'
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