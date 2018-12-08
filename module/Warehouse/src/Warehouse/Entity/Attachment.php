<?php
namespace Warehouse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Warehouse\Repository\AttachmentRepository")
 * @ORM\Table(name="attachment")
 */
class Attachment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\Column(type="string", length=1)
     */
    protected $defaultphoto;
    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $description;
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $filename;
    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $mime;
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $path;
    /**
     * @ORM\Column(type="integer")
     */
    protected $size;

    /**
     * @return $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return $description
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
     * @return string
     */
    public function getFileName()
    {
        return $this->filename;
    }

    /**
     * @param string filename
     */
    public function setFileName($fileName)
    {
        $this->filename = $fileName;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @param string mime
     */
    public function setMime($mime)
    {
        $this->mime = $mime;
    }

    /**
     * @return $size
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param integer $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return $defaultphoto
     */
    public function getDefaultPhoto()
    {
        return $this->defaultphoto;
    }

    /**
     * @param string $defaultphoto
     */
    public function setDefaultPhoto($defaultphoto)
    {
        $this->defaultphoto = $defaultphoto;
    }

    /**
     * @return datetime
     */
    public function getCreationDate()
    {
        return $this->creationdate;
    }

    /**
     * @param datatetime creationdate
     */
    public function setCreationDate($creationdate)
    {
        $this->creationdate = $creationdate;
    }
}
