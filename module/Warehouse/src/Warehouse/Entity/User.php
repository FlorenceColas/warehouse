<?php
/**
 * User: FlorenceColas
 * Date: 05/01/2017
 * Version: 1.00
 * User: Entity corresponding to user table
 * Properties:
 *      - id
 *      - logonname
 *      - name
 *      - password
 *      - lastconnection
 *      - status
 *      - access
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User
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
    protected $logonName;
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $password;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastconnection;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $status;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $access;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string $logonName
     */
    public function getLogonName()
    {
        return $this->logonName;
    }

    /**
     * @param string $logonName
     */
    public function setLogonName($logonName)
    {
        $this->logonName = $logonName;
    }

    /**
     * $return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return $lastconnection
     */
    public function getLastconnection()
    {
        return $this->lastconnection;
    }

    /**
     * @param datetime $lastconnection
     */
    public function setLastconnection($lastconnection)
    {
        $this->lastconnection = $lastconnection;
    }

    /**
     * @return $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param mixed $access
     */
    public function setAccess($access)
    {
        $this->access = $access;
    }

}

?>