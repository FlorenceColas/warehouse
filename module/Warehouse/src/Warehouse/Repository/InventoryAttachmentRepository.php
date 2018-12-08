<?php

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;

class InventoryAttachmentRepository extends EntityRepository
{
    /**
     * Return the attachment corresponding to the attachment id in parameter
     * @param int $id
     * @return StockAttachment
     */
    public function findByAttachmentId($id) {
        $result = $this->findBy(array('id' => $id));
        return $result;
    }

    /**
     * Return the attachment corresponding to the filename in parameter
     * @param String $filename
     * @return StockAttachment
     */
    public function findByAttachmentName($filename) {
        $result = $this->findBy(array('filename' => $filename));
        return $result;
    }

    /**
     * Return the attachment for defaultphoto corresponding to the recipe id in parameter
     * @param String $recipeid
     * @return StockAttachment
     */
    public function findByStockIdDefaultPhoto($recipeid) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
            ->from('\Warehouse\Entity\Attachment', 'a')
            ->leftJoin('\Warehouse\Entity\StockAttachment', 'ra', \Doctrine\ORM\Query\Expr\Join::WITH, 'a.id = ra.attachment_id')
            ->where('ra.stock_id = ' . $recipeid)
            ->andWhere('a.defaultphoto = 1');

        $query = $qb->getQuery()->getResult();

        return $query;

    }

    /**
     * Return a list of attachments corresponding to the recipe id in parameter
     * @param integer $id
     * @return StockAttachment[]
     */
    public function findByStockId($id) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
            ->from('\Warehouse\Entity\StockAttachment', 'ra')
            ->leftJoin('\Warehouse\Entity\Attachment', 'a', \Doctrine\ORM\Query\Expr\Join::WITH, 'a.id = ra.attachment_id')
            ->where('ra.stock_id='.$id);
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return all attachments order by description
     * @return StockAttachment
     */
    public function findAllOrderByDescription() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
            ->from('\Warehouse\Entity\StockAttachment', 'a')
            ->orderBy('a.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }
}
