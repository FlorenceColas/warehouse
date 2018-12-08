<?php
/**
 * User: FlorenceColas
 * Date: 15/11/16
 * Version: 1.00
 * RecipeAttachmentRepository: Repository for recipeattachment table. It contains the following functions:
 *      - findByAttachmentId: return the attachment corresponding to the attachment id in parameter
 *      - findByAttachmentName: return the attachment corresponding to the filename in parameter
 *      - findByRecipeIdDefaultPhoto: return the attachment for defaultphoto corresponding to the recipe id in parameter
 *      - findByRecipeId: return a list of attachments corresponding to the recipe id in parameter
 *      - findAllOrderByDescription: return all attachments order by description
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;

class RecipeAttachmentRepository extends EntityRepository
{
    /**
     * Return the attachment corresponding to the attachment id in parameter
     * @param int $id
     * @return RecipeAttachment
     */
    public function findByAttachmentId($id) {
        $result = $this->findBy(array('id' => $id));
        return $result;
    }

    /**
     * Return the attachment corresponding to the filename in parameter
     * @param String $filename
     * @return RecipeAttachment
     */
    public function findByAttachmentName($filename) {
        $result = $this->findBy(array('filename' => $filename));
        return $result;
    }

    /**
     * Return the attachment for defaultphoto corresponding to the recipe id in parameter
     * @param String $recipeid
     * @return Attachment
     */
    public function findByRecipeIdDefaultPhoto($recipeid) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
            ->from('\Warehouse\Entity\Attachment', 'a')
            ->leftJoin('\Warehouse\Entity\RecipeAttachment', 'ra', \Doctrine\ORM\Query\Expr\Join::WITH, 'a.id = ra.attachment_id')
            ->where('ra.recipes_id = ' . $recipeid)
            ->andWhere('a.defaultphoto = 1');

        $query = $qb->getQuery()->getResult();

        return $query;

    }

    /**
     * Return a list of attachments corresponding to the recipe id in parameter
     * @param integer $id
     * @return RecipeAttachment[]
     */
    public function findByRecipeId($id) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
            ->from('\Warehouse\Entity\RecipeAttachment', 'ra')
            ->leftJoin('\Warehouse\Entity\Attachment', 'a', \Doctrine\ORM\Query\Expr\Join::WITH, 'a.id = ra.attachment_id')
            ->where('ra.recipes_id='.$id);
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Return all attachments order by description
     * @return RecipeAttachment
     */
    public function findAllOrderByDescription() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
            ->from('\Warehouse\Entity\RecipeAttachment', 'a')
            ->orderBy('a.description');
        $query = $qb->getQuery()->getResult();

        return $query;
    }
}