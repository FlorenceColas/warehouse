<?php

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;

class AttachmentRepository extends EntityRepository
{
    /**
     * Return the attachment corresponding to the attachment id in parameter
     * @param int $id
     * @return Attachment
     */
    public function findByAttachmentId($id) {
        $result = $this->findBy(array('id' => $id));
        return $result;
    }

    /**
     * Return the attachment corresponding to the filename in parameter
     * @param String $filename
     * @return Attachment
     */
    public function findByAttachmentName($filename) {
        $result = $this->findBy(array('filename' => $filename));
        return $result;
    }

    /**
     * Return the attachment for defaultphoto corresponding to the stock id in parameter
     * @param int $id
     * @return Attachment
     */
    public function findByIdDefaultPhoto($id) {
        $result = $this->findBy(array('id' => $id, 'defaultphoto' => '1'));
        return $result;
    }

    /**
     * Return a list of attachments corresponding to the stock id in parameter
     * @param integer $id
     * @return Attachment[]
     */
    public function findByStockId($id) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
            ->from('\Warehouse\Entity\Attachment', 'a')
            ->where('a.stock='.$id);
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return all attachments order by description
     * @return Attachments
     */
    public function findAllOrderByDescription() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
            ->from('\Warehouse\Entity\Attachment', 'a')
            ->orderBy('a.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }
}