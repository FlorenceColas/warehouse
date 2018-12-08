<?php
/**
 * User: FlorenceColas
 * Date: 20/02/16
 * Version: 1.00
 * IngredientFieldset: Fieldset which contains the recipe ingredients form fields
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Fieldset;

use Warehouse\Entity\Ingredient;
use Warehouse\Enum\EnumTableSettings;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class IngredientFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('ingredients');

        $this->setHydrator(new DoctrineHydrator($objectManager, 'Warehouse\Entity\Ingredient', false));
        $this->setObject(new Ingredient());

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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'stockmergement_id',
            'options' => array(
                'label' => ''
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Number',
            'name' => 'quantity',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'min' => '0',
                'max' => '999999',
                'step' => '0.25',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'measureunit_id',
            'options' => array(
                'label' => ''
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Textarea',
            'name'    => 'description',
            'options' => array(
                'label' => ''
            ),
            'attributes' => [
                'maxlength' => 255,
                'cols' => 50,
                'rows' => 1,
            ]
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'recipes_id'
        ));

        //Initialise la liste des articles
        $stockMergement = $objectManager->getRepository('Warehouse\Entity\StockMergement')->findAllOrderByDescription();
        $options = array(""=>"");
        foreach($stockMergement as $st) {
            $options[$st->getId()] = $st->getDescription();
        }
        $this->get('stockmergement_id')->setValueOptions($options);

        //load MeasureUnit list
        $unit = $objectManager->getRepository('Warehouse\Entity\MeasureUnit')->findAllOrderByDescription(EnumTableSettings::MEASUREUNIT);
        $options = array();
        foreach($unit as $st) {
            $options[$st->getId()] = $st->getUnit();
        }
        $this->get('measureunit_id')->setValueOptions($options);

    }

    public function getInputFilterSpecification()
    {
        return array(
            'stockmergement_id' => array(
                'required' => true,
            ),
            'description' => array(
                'required' => false,
            ),
            'quantity' => array(
                'required' => false,
            ),
            'measureunit_id' => array(
                'required' => false,
            ),
            'sequence' => array(
                'required' => false,
            ),
        );
    }


}