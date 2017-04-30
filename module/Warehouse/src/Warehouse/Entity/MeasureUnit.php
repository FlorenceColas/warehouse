<?php
/**
 * User: FlorenceColas
 * Date: 13/11/16
 * Version: 1.00
 * MeasureUnit: Entity corresponding to measureunit table
 * Properties:
 *      - id
 *      - description
 *      - unit
 *      - comparaison
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\SettingsRepository")
 * @ORM\Table(name="measureunit")
 */
 class MeasureUnit
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $description;
    /**
     * @ORM\Column(type="string", length=25)
     */
    protected $unit;
     /**
      * @ORM\Column(type="integer")
      */
    protected $comparaison;
     /**
      * @ORM\Column(type="integer")
      */
     protected $useinstock;

     /**
      * @return mixed
      */
     public function getUseinstock()
     {
         return $this->useinstock;
     }

     /**
      * @param mixed $useinstock
      */
     public function setUseinstock($useinstock)
     {
         $this->useinstock = $useinstock;
     }

    /**
     * @return $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return $description
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @return $unit
     */
    public function getUnit()
    {
        return $this->unit;
    }
    
    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    /**
     * @param string $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

     /**
      * @return $comparaison
      */
     public function getComparaison()
    {
        return $this->comparaison;
    }

     /**
      * @param integer $comparaison
      */
     public function setComparaison($comparaison)
     {
         $this->comparaison = $comparaison;
     }
}

?>