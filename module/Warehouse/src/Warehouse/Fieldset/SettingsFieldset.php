<?php
/**
 * User: FlorenceColas
 * Date: 29/02/16
 * Version: 1.00
 * SettingsFieldset: Fieldset which contains the setting form fields
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Fieldset;

use Warehouse\Entity\Area;
use Warehouse\Entity\MeasureUnit;
use Warehouse\Entity\Section;
use Warehouse\Entity\Supplier;
use Warehouse\Enum\EnumTableSettings;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class SettingsFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager, $table)
    {
        parent::__construct($table);

        $this->setHydrator(new DoctrineHydrator($objectManager, 'Warehouse\Entity\\'.$table, false));
        switch ($table){
            case EnumTableSettings::AREA:
                $this->setObject(new Area());
                break;
            case EnumTableSettings::MEASUREUNIT:
                $this->setObject(new MeasureUnit());
                break;
            case EnumTableSettings::SUPPLIER:
                $this->setObject(new Supplier());
                break;
            case EnumTableSettings::SECTION:
                $this->setObject(new Section());
                break;
            case EnumTableSettings::RECIPE_CATEGORY:
                $this->setObject();
                break;
        }

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'description',
            'options' => array(
                'label' => ''
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'abbreviation',
            'options' => array(
                'label' => ''
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'useinstock',
            'options' => array(
                'label' => ''
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'area_id',
            'options' => array(
                'label' => ''
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return [
            'description' => array(
                'required' => true,
            ),
            'abbreviation' => array(
                'required' => false,
            ),
            'useinstock' => array(
                'required' => false,
            ),
            'area_id' => array(
                'required' => false,
            ),
        ];
    }
}