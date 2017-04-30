<?php
/**
 * User: FlorenceColas
 * Date: 08/03/16
 * Version: 1.00
 * CriteriaRecipe: Model used for recipe list pagination
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\ArraySerializableInterface;

class CriteriaRecipe implements ArraySerializableInterface, InputFilterAwareInterface
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @var string[]
     */
    protected $category;

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
     * @return array string[]
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param array $category
     */
    public function setCategory(array $category)
    {
        $this->category = $category;
    }

    /**
     * Exchange internal values from provided array
     * @param  array $array
     * @return void
     */
    public function exchangeArray(array $array)
    {
        $this->description = $array['description'];
        if (array_key_exists('category', $array))
            $this->category = $array['category'];
        else
            $this->category = [''];
    }

    /**
     * Return an array representation of the object
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

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
                'name'     => 'category',
                'required' => false,
            ]);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}