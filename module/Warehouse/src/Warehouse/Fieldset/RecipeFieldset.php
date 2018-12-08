<?php
namespace Warehouse\Fieldset;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\Hydrator\ClassMethods as ClassMethodsHydrator;

use Warehouse\Entity\Recipe;
use Warehouse\Enum\EnumTableSettings;
use Zend\Form\Fieldset;
use Zend\Form\Element\Collection;
use Zend\InputFilter\InputFilterProviderInterface;

class RecipeFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('recipe');

        $this->setHydrator(new ClassMethodsHydrator(false));
//        $this->setHydrator(new DoctrineHydrator($objectManager,'Warehouse\Entity\Recipe',false));

        $this->setObject(new Recipe());

        $this->setLabel('Recipe');

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'description',
            'options' => array(
                'label' => 'Recipe: '
            ),
            'attributes' => [
                'cols' => 90,
                'rows' => 1,
                'maxlength' => 255
            ]
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Number',
            'name' => 'serves',
            'options' => array(
                'label' => 'Serves'
            ),
            'attributes' => array(
                'min' => '0',
                'max' => '20',
                'step' => '1', // default step interval is 1
            )
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Time',
            'name'    => 'preparationTime',
            'options' => [
                'label' => 'Preparation time (hh:mm): ',
                'format' => 'H:i'
            ],
            'attributes' => [
                'min' => '00:00',
                'max' => '23:59',
                'step' => '60', // seconds; default step interval is 60 seconds
            ],
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Time',
            'name'    => 'totalTime',
            'options' => [
                'label' => 'Total time (hh:mm): ',
                'format' => 'H:i'
            ],
             'attributes' => [
                'min' => '00:00',
                'max' => '23:59',
                'step' => '60', // seconds; default step interval is 60 seconds
            ],
        ));

        $ingredients = new Collection('ingredients');
        $ingredients->setLabel('Ingredients')
            ->setOptions(array(
                'count'          => 1,
                'allow_add'      => true,
                'allow_remove'   => true,
                'should_create_template' => true,
                'template_placeholder' => '__ingredients__',
                'target_element' => new IngredientFieldset($objectManager),
            ));
        $this->add($ingredients);

        $instructions = new Collection('instructions');
        $instructions->setLabel('Instructions')
            ->setOptions(array(
                'count'          => 1,
                'allow_add'      => true,
                'allow_remove'   => true,
                'should_create_template' => true,
                'template_placeholder' => '__instructions__',
                'target_element' => new InstructionFieldset($objectManager),
            ));
        $this->add($instructions);

        $this->add(array(
            'type'    => 'Zend\Form\Element\Textarea',
            'name'    => 'note',
            'options' => [
                'label' => 'Note: '
            ],
            'attributes' => [
                'cols' => 120,
                'rows' => 3,
                'maxlength' => 500
            ]
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'category_id',
            'options' => array(
                'label' => ''
            ),
        ));

        //load RecipeCategory list
        $category = $objectManager->getRepository('Warehouse\Entity\Category')->findAllOrderByDescription(EnumTableSettings::RECIPE_CATEGORY);
        $options = array();
        foreach($category as $st) {
            $options[$st->getId()] = $st->getDescription();
        }
        $this->get('category_id')->setValueOptions($options);

    }

    /**
     * Define InputFilterSpecifications
     * @access public
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(
            'description' => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
                'properties' => array(
                    'required' => true
                )
            ),
            'serves' => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
                'properties' => array(
                    'required' => true
                )
            ),
            'preparationTime' => array(
                'required' => true,
/*                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
*/
                'properties' => array(
                    'required' => true
                )
            ),
            'totalTime' => array(
                'required' => true,
 /*               'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
 */
                'properties' => array(
                    'required' => true
                )
            ),
            'category_id' => [
                'required' => true,
            ]
        );
    }
}