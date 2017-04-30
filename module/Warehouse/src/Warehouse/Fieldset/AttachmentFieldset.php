<?php
/**
 * User: FlorenceColas
 * Date: 28/03/16
 * Version: 1.00
 * AttachmentFieldset: Fieldset which contains the article attachment form fields
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Fieldset;

use Warehouse\Entity\Attachment;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


class AttachmentFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('attachments');

        $this->setHydrator(new DoctrineHydrator($objectManager, 'Warehouse\Entity\Attachment', false));
        $this->setObject(new Attachment());

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'stock_id',
            'options' => array(
                'label' => ''
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'description',
            'options' => array(
                'label' => ''
            )
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'path',
            'options' => array(
                'label' => ''
            )
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'filename',
            'options' => array(
                'label' => ''
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'mime',
            'options' => array(
                'label' => '',
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'size',
            'options' => array(
                'label' => ''
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'defaultphoto',
            'options' => array(
                'label' => '',
            ),
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'description' => array(
                'required' => false,
            ),
            'filename' => array(
                'required' => true,
            ),
            'path' => array(
                'required' => true,
            ),
            'size' => array(
                'required' => true,
            ),
            'mime' => array(
                'required' => true,
            ),
            'defaultphoto' => array(
                'required' => true,
            ),
        );
    }
}