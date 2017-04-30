<?php
/**
 * User: FlorenceColas
 * Date: 05/12/16
 * Version: 1.00
 * Appsettings: Entity corresponding to appsettings table
 * Properties:
 *      - id
 *      - settingreference
 *      - settingvalue
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\AppsettingsRepository")
 * @ORM\Table(name="appsettings")
 */
class Appsettings
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
    protected $settingreference;
    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $settingvalue;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getSettingReference()
    {
        return $this->settingreference;
    }

    /**
     * @param mixed $settingreference
     */
    public function setSettingReference($settingreference)
    {
        $this->settingreference = $settingreference;
    }

    /**
     * @return mixed
     */
    public function getSettingValue()
    {
        return $this->settingvalue;
    }

    /**
     * @param mixed $settingvalue
     */
    public function setSettingValue($settingvalue)
    {
        $this->settingvalue = $settingvalue;
    }

}