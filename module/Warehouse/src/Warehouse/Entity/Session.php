<?php
/**
 * User: FlorenceColas
 * Date: 09/02/16
 * Version: 1.00
 * Session: Entity corresponding to session table
 * Properties:
 *      - id
 *      - userid
 *      - creationdate
 *      - updatedate
 *      - data
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM; // Pour utiliser les annotations


/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\SessionRepository")
 * @ORM\Table(name="session")
 */
class Session
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $id;
    /**
     * @ORM\Column(type="integer")
     */
    protected  $userid;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $updatedate;
    /**
     * @ORM\Column(type="blob")
     */
    protected $data;

    /**
     * @return $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return $userid
     */
    public function getUserId()
    {
        return $this->userid;
    }

    /**
     * @param integer $userid
     */
    public function setUserId($userid)
    {
        $this->userid = $userid;
    }

    /**
     * @return $creationdate
     */
    public function getCreationDate()
    {
        return $this->creationdate;
    }

    /**
     * @param datetime $creationdate
     */
    public function setCreationDate($creationdate)
    {
        $this->creationdate = $creationdate;
    }

    /**
     * @return datetime
     */
    public function getUpdateDate()
    {
        return $this->updatedate;
    }

    /**
     * @param datetime $updatedate
     */
    public function setUpdateDate($updatedate)
    {
        $this->updatedate = $updatedate;
    }

    /**
     * @return Array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param Array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

}

?>