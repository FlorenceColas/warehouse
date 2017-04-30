<?php
/**
 * User: FlorenceColas
 * Date: 15/02/2017
 * Version: 1.00
 * StockinterfaceController: manage stock in/out menu displayed. It contains the following actions:
 *      - list: stock in/out list
 *      - update: update the current element (quantity, unit, inventory)
 *      - delete: delete the current element
 *      - integrate: integrate in inventory
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *  29/04/2017 - integrate session/authentication
 */
namespace Warehouse\Controller;

use Warehouse\Enum\EnumStockMovementType;
use Warehouse\Enum\EnumTableSettings;
use Zend\Mvc\Controller\AbstractActionController;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\ViewModel;

class StockinterfaceController extends AbstractActionController
{
    protected $entityManager;
    protected $authservice;
    protected $audittrailservice;

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

    private function getAuthService()
    {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        return $this->authservice;
    }

    private function getAuditTrailService()
    {
        if (! $this->audittrailservice) {
            $this->audittrailservice = $this->getServiceLocator()->get('AuditTrailService');
        }
        return $this->audittrailservice;
    }

    /*
     * Display the inventory (list action)
     */
    public function listAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $stockinterface = $this->getEntityManager()->getRepository('Warehouse\Entity\StockInterface')->findAllOrderBySensDescription();
                $units = $this->getEntityManager()->getRepository('Warehouse\Entity\MeasureUnit')->findAllOrderByDescription(EnumTableSettings::MEASUREUNIT);
                $stock = $this->getEntityManager()->getRepository('Warehouse\Entity\Stock')->findAllMergedOrderByMergePreferedDescription();

                $stockNetQuantity = array();
                $stockQuantity = array();
                foreach($stock as $s){
                    $stockNetQuantity[$s->getId()] = $s->getNetquantity();
                    $stockQuantity[$s->getId()] = $s->getQuantity();
                }

                //add data to the ViewModel
                $viewModel->setVariables([
                    'stockinterface' => $stockinterface,
                    'units' => $units,
                    'stock' => $stock,
                    'stockNetQty' => $stockNetQuantity,
                    'stockQty' => $stockQuantity,
                ]);

                return $viewModel;
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        } else {
            //redirect to login page
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
            return $this->getResponse();
        }
    }

    public function updateAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $stockId = $this->params()->fromQuery('stock','');
                $qty = $this->params()->fromQuery('quantity','');
                $unitId = $this->params()->fromQuery('unit','');
                $qtyToInt = $this->params()->fromQuery('quantitytointegrate','');
                $unitIdToInt = $this->params()->fromQuery('unittointegrate','');

                $stockInterfaceId = $this->params()->fromRoute('id', 0);
                $stockInterface = $this->getEntityManager()->getRepository('Warehouse\Entity\StockInterface')->findByStockInterfaceId($stockInterfaceId);
                $stockInt = $stockInterface[0];

                if ($stockId != '') {
                    $stock = $this->getEntityManager()->getRepository('Warehouse\Entity\Stock')->findByStockId($stockId);
                    if ($stock[0]->getQuantity() <= 0){
                        $qty = '<font color="red">'.$stock[0]->getQuantity().'</font>' . ' x ' .$stock[0]->getNetquantity();
                    } else {
                        $qty = '<font color="green">'.$stock[0]->getQuantity().'</font>' . ' x ' .$stock[0]->getNetquantity();
                    }
                    $stockInt->setStock($stock[0]);
                } elseif ($qty != '') {
                    $stockInt->setQuantity($qty);
                } elseif ($unitId != '') {
                    $unit = $this->getEntityManager()->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($unitId, EnumTableSettings::MEASUREUNIT);
                    $stockInt->setUnit($unit[0]);
                } elseif ($qtyToInt != '') {
                    $stockInt->setQuantitytointegrate($qtyToInt);
                } elseif ($unitIdToInt != '') {
                    $unit = $this->getEntityManager()->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($unitIdToInt, EnumTableSettings::MEASUREUNIT);
                    $stockInt->setUnittointegrate($unit[0]);
                }
                $this->getEntityManager()->persist($stockInt);
                $this->getEntityManager()->flush();

                $response = $this->getResponse();
                $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
                $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "netqty" => $qty)));
                return $response;
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        } else {
            //redirect to login page
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
            return $this->getResponse();
        }
    }

    /**
     * Delete one or more article from the stockinterface list
     */
    public function deleteAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $id = $this->params()->fromRoute('id', 0);
                if ($id != 0) {
                    //read the record in DB
                    $stockInterface = $this->getEntityManager()->getRepository('Warehouse\Entity\StockInterface')->findByStockInterfaceId($id);
                    $si = $stockInterface[0];
                    //remove record in DB
                    $this->getEntityManager()->remove($si);
                    $this->getEntityManager()->flush();
                }
                $response = $this->getResponse();
                $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
                $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" => '')));
                return $response;
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        } else {
            //redirect to login page
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
            return $this->getResponse();
        }
    }

    /**
     * Integrate the inventory elements listed in the inventory table
     */
    public function integrateAction() {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $stockInterface = $this->getEntityManager()->getRepository('Warehouse\Entity\StockInterface')->findAllOrderBySensDescription();
                foreach ($stockInterface as $sI) {
                    $stock = $this->getEntityManager()->getRepository('Warehouse\Entity\Stock')->findByStockId($sI->getStock()->getId());
                    if ($sI->getSens() == EnumStockMovementType::MOVEMENT_SHOP_ADD){
                        $stock[0]->setQuantity($stock[0]->getQuantity() + $sI->getQuantitytointegrate());
                    } else {
                        $stock[0]->setQuantity($stock[0]->getQuantity() - $sI->getQuantitytointegrate());
                    }
                    $this->getEntityManager()->persist($stock[0]);
                    $this->getEntityManager()->flush();

                    //read the quantity in stock table
                    $stockQty = $this->getEntityManager()->getRepository('Warehouse\Entity\Stock')->findCountQuantityForMergeId($sI->getMerge()->getId());
                    $quantityMerge = $stockQty[0]['quantity'];
                    //update stock quantity in StockMergement table
                    $stockMerge = $this->getEntityManager()->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($sI->getMerge()->getId());
                    $stockMerge[0]->setNetquantity(intval($quantityMerge));
                    $this->getEntityManager()->persist($stockMerge[0]);
                    $this->getEntityManager()->flush();
                }

                $q = $this->getEntityManager()->createQuery('delete from Warehouse\Entity\StockInterface');
                $q->execute();

                $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockinterface', 'action' => 'list']);
                return $this->getResponse();
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        } else {
            //redirect to login page
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
            return $this->getResponse();
        }
    }
}