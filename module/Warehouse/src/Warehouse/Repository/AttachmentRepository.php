<?php
/**
 * User: FlorenceColas
 * Date: 15/11/16
 * Version: 1.00
 * AttachmentRepository: Repository for attachement table. It contains the following functions:
 *      - findByAttachmentId: return the attachment corresponding to the attachment id in parameter
 *      - findByAttachmentName: return the attachment corresponding to the filename in parameter
 *      - findByStockIdDefaultPhoto: return the attachment for defaultphoto corresponding to the stock id in parameter
 *      - findByStockId: return a list of attachments corresponding to the stock id in parameter
 *      - findAllOrderByDescription: return all attachments order by description
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

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
     * @param String $stockid
     * @return Attachment
     */
    public function findByStockIdDefaultPhoto($stockid) {
        $result = $this->findBy(array('stock' => $stockid, 'defaultphoto' => '1'));
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