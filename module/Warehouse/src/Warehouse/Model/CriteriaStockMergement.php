<?php
/**
 * User: FlorenceColas
 * Date: 07/02/16
 * Version: 1.00
 * CriteriaStock: Model used for stock list pagination
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Model;

use Zend\Stdlib\ArraySerializableInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class CriteriaStockMergement implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $availability;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string[]
     */
    protected $section;

    protected $area;

    protected $inputFilter;

    /**
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return int $availability
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @param int $availability
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;
    }

    /**
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**s
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string $area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param string $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return array string[]
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param array $section
     */
    public function setSection(array $section)
    {
        $this->section = $section;
    }

    /**
     * Exchange internal values from provided array
     * @param  array $array
     * @return void
     */
    public function exchangeArray(array $array)
    {
        $this->description = $array['description'];
        $this->availability = $array['availability'];
        $this->area = $array['area'];
        $this->status = $array['status'];
        if (array_key_exists('section', $array))
            $this->section = $array['section'];
        else
            $this->section = [''];
    }

    /**
     * Return an array representation of the object
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    // Add content to these methods:
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add([
                'name'     => 'description',
                'required' => false,
            ]);

            $inputFilter->add([
                'name' => 'availability',
                'required' => true,
            ]);

            $inputFilter->add([
                'name' => 'area',
                'required' => true,
            ]);

            $inputFilter->add([
                'name'     => 'status',
                'required' => true,
                'filters'  => [
                  //  array('name' => 'StripTags'),
                  //  array('name' => 'StringTrim'),
                ],
                'validators' => [
              /*      array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,

                    ),*/
                ],
            ]);

            $inputFilter->add([
                'name'     => 'section',
                'required' => false,
            ]);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}