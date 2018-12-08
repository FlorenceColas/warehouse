<?php
/**
 * User: FlorenceColas
 * Date: 02/04/16
 * Version: 1.00
 * ServiceController: REST services
 * It contains the following actions:
 *      - shoppinglist: return the shopping list (json)
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Rest;

use Doctrine\ORM\EntityManager;
use \Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ServiceController extends AbstractActionController
{
    protected $entityManager;

    /*
     * @param EntityManager $em
     */
    private function setEntityManager(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    /*
     * @return EntityManager
     */
    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->setEntityManager($this->getServiceLocator()->get('doctrine.entitymanager.orm_default'));
        }
        return $this->entityManager;
    }

    /**
     * Return the shopping list
     * @return JsonModel
     */
    public function shoppinglistAction(){
        $shoppingList = $this->getEntityManager()->getRepository('Warehouse\Entity\ShoppingList')->findAllOrderBySectionDescription();

        $resultArray = array();

        for ($i=0;$i<count($shoppingList);$i++){
            $tempArray = [
                "section" => $shoppingList[$i]->getSection()->getDescription(),
                "description" => $shoppingList[$i]->getDescription(),
                "supplier" => $shoppingList[$i]->getSupplier()->getDescription(),
                "priority" => $shoppingList[$i]->getPriority(),
                "quantity" => $shoppingList[$i]->getQuantity(),
                "unit" => $shoppingList[$i]->getMeasureUnit()->getDescription(),
                "barcodeid" => $shoppingList[$i]->getStock()->getDescription(),
                "area" => $shoppingList[$i]->getArea()->getDescription(),
            ];
            array_push($resultArray, $tempArray);
        }

        return new JsonModel($resultArray);
    }
}