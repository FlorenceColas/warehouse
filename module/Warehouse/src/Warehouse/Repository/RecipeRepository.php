<?php
namespace Warehouse\Repository;

use Doctrine\ORM\EntityRepository;
use Warehouse\Entity\Recipe;

class RecipeRepository extends EntityRepository
{
    /**
     * @param  array $criterias @optional
     * @return Recipe[]
     */
    public function getFindRecipesByCriterias(array $criterias = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('A.id, A.description, A.serves, DATE_FORMAT(A.preparationTime, \'%H:%i\') as preparationTime, DATE_FORMAT(A.totalTime, \'%H:%i\') as totalTime, C.filename')
            ->from('\Warehouse\Entity\Recipe', 'A')
            ->leftJoin('\Warehouse\Entity\RecipeAttachment', 'B', \Doctrine\ORM\Query\Expr\Join::WITH, 'B.recipes_id = A.id')
            ->leftJoin('\Warehouse\Entity\Attachment'      , 'C', \Doctrine\ORM\Query\Expr\Join::WITH, 'C.id = B.attachment_id')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('C.defaultphoto', 1),
                $qb->expr()->isNull('C.defaultphoto')
            ));

        if (null != $criterias) {
            $qb->andWhere($qb->expr()->like('A.description', $qb->expr()->literal('%' . $criterias['description'] . '%')));
            if (isset($criterias['categories']) and count($criterias['categories']) > 0) {
                $qb->andWhere($qb->expr()->in('A.category', $criterias['categories']));
            }
        }

        $qb->orderBy('A.description', 'ASC');
        $query = $qb->getQuery()->getArrayResult();

        return $query;
    }

    /**
     * @param int $id
     * @return Recipe
     */
    public function findByRecipeId(int $id) {
        $result = $this->findBy(['id' => $id]);
        return $result;
    }
}
