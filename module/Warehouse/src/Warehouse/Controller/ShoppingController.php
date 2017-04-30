<?php
/**
 * User: FlorenceColas
 * Date: 11/03/16
 * Version: 1.00
 * ShoppingController: manage the shopping list
 * It contains the following actions:
 *      - list: return the setting table content
 *      - update: persist modification (quantity, unit, priority)
 *      - add: add a new article in the shopping list
 *      - validate: persist the status modification
 *      - delete: delete one or more article from the shopping list
 *      - exportxls: export the shopping list in xls file
 *      - sendtostockinterface: send the shopping list to the stock interface
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *  29/04/2017 - integrate session/authentication
 */

namespace Warehouse\Controller;

use Doctrine\ORM\EntityManager;
use Warehouse\Entity\ShoppingList;
use Warehouse\Entity\StockInterface;
use Warehouse\Enum\EnumAppSettingsReferences;
use Warehouse\Enum\EnumShoppingListStatus;
use Warehouse\Enum\EnumStockMovementType;
use Warehouse\Enum\EnumStockSens;
use Warehouse\Enum\EnumTableSettings;
use Warehouse\Enum\EnumUnit;
use Warehouse\Form\ShoppingForm;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Mime\Mime;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ShoppingController extends AbstractActionController
{
    protected $entityManager;
    protected $mailservice;
    protected $authservice;
    protected $audittrailservice;

    public function setEntityManager(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    public function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->setEntityManager($this->getServiceLocator()->get('doctrine.entitymanager.orm_default'));
        }
        return $this->entityManager;
    }

    private function getMailService()
    {
        if (! $this->mailservice) {
            $this->mailservice = $this->getServiceLocator()->get('MailService');
        }
        return $this->mailservice;
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

    /**
     * Display the shopping list
     * @return ViewModel
     */
    public function listAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $shoppingList = $this->getEntityManager()->getRepository('Warehouse\Entity\ShoppingList')->findAllOrderBySectionDescription();
                $units = $this->getEntityManager()->getRepository('Warehouse\Entity\MeasureUnit')->findAllOrderByDescription(EnumTableSettings::MEASUREUNIT);
                $stock = $this->getEntityManager()->getRepository('Warehouse\Entity\StockMergement')->findAllOrderByDescription();
                $arrStock = array();
                foreach ($stock as $s) {
                    $arrStock[$s->getId()] = $s->getDescription();
                }

                //add data to the ViewModel
                $viewModel->setVariables([
                    'shoppinglist' => $shoppingList,
                    'units' => $units,
                    'arrstock' =>$arrStock,
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

    /**
     * persist modification: unit, quantity or priority
     */
    public function updateAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $id = $this->params()->fromRoute('id', 0);
                $unitId = $this->params()->fromQuery('unit','');
                $quantity = $this->params()->fromQuery('quantity','');
                $priority = $this->params()->fromQuery('priority','');
                $sendtostock = $this->params()->fromQuery('sendtostock','');

                $shoppinglist = $this->getEntityManager()->getRepository('Warehouse\Entity\ShoppingList')->findByShoppingListId($id);
                $sl = $shoppinglist[0];

                $update = false;
                if ($unitId !== ""){
                    $set = $this->getEntityManager()->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($unitId,EnumTableSettings::MEASUREUNIT);
                    $unit = $set[0];
                    $sl->setUnit($unit);
                    $update = true;
                } elseif ($quantity !== ""){
                    if ($quantity != $sl->getQuantity()) {
                        $sl->setQuantity($quantity);
                        $update = true;
                    }
                } elseif ($priority !== ""){
                    $sl->setPriority($priority);
                    $update = true;
                } elseif ($sendtostock !== ""){
                    $sl->setSendtostock($sendtostock);
                    $update = true;
                }

                if ($update) {
                    $sl->setStatus(EnumShoppingListStatus::SHOPPING_LIST_STATUS_NEW_TO_BUY);
                    $this->getEntityManager()->persist($sl);
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
     * Add a new article in the shopping list
     */
    public function addAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $id = $this->params()->fromRoute('id', 0);

                //verify if this article is not already existing in the shopping list
                //if yes, just increment the quantity
                $shoppinglist = $this->getEntityManager()->getRepository('Warehouse\Entity\ShoppingList')->findByStockMergementId($id);
                if (isset($shoppinglist) and $shoppinglist!= null and count($shoppinglist[0]) <> 0) {
                    $sl = $shoppinglist[0];
                    $sl->setQuantity($sl->getQuantity() + 1);
                } else {
                    $stock = $this->getEntityManager()->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($id);
                    $sl = new ShoppingList();
                    $sl->setStockmergement($stock[0]);
                    $sl->setDescription($stock[0]->getDescription());
                    $sl->setQuantity(1);
                    $defaultUnitId = $this->getEntityManager()->getRepository('Warehouse\Entity\Appsettings')->findByReference(EnumAppSettingsReferences::APP_SETTINGS_DEFAULT_SHOPPING_LIST_UNIT_ID)->getResult();
                    $unit = $this->getEntityManager()->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($defaultUnitId[0]->getSettingvalue(), EnumTableSettings::MEASUREUNIT);
                    $sl->setUnit($unit[0]);
                    $sl->setSection($stock[0]->getSection());
                    $sl->setSupplier($stock[0]->getSupplier());
                    $sl->setPriority(1);
                    $sl->setArea($stock[0]->getArea());
                    $sl->setSendtostock(1);
                }
                $sl->setStatus(EnumShoppingListStatus::SHOPPING_LIST_STATUS_NEW_TO_BUY);
                $this->getEntityManager()->persist($sl);
                $this->getEntityManager()->flush();

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
     * Update the status of the current article: New, To Buy, Bought
     */
    public function validateAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $id = $this->params()->fromRoute('id', 0);

                $shoppinglist = $this->getEntityManager()->getRepository('Warehouse\Entity\ShoppingList')->findByShoppingListId($id);
                $sl = $shoppinglist[0];

                if ($sl->getStatus() == EnumShoppingListStatus::SHOPPING_LIST_STATUS_NEW_TO_BUY) $status = EnumShoppingListStatus::SHOPPING_LIST_STATUS_TO_BUY;
                elseif ($sl->getStatus() == EnumShoppingListStatus::SHOPPING_LIST_STATUS_TO_BUY) $status = EnumShoppingListStatus::SHOPPING_LIST_STATUS_BOUGHT;
                else $status = EnumShoppingListStatus::SHOPPING_LIST_STATUS_TO_BUY;

                $sl->setStatus($status);

                $this->getEntityManager()->persist($sl);
                $this->getEntityManager()->flush();

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
     * Delete one or more article from the shopping list
     */
    public function deleteAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $id = $this->params()->fromRoute('id', 0);
                if ($id != 0) {
                    //read the record in DB
                    $shoppinglist = $this->getEntityManager()->getRepository('Warehouse\Entity\ShoppingList')->findByShoppingListId($id);
                    $sl = $shoppinglist[0];
                    //remove record in DB
                    $this->getEntityManager()->remove($sl);
                    $this->getEntityManager()->flush();
                } else {
                    //delete all records
                    $q = $this->getEntityManager()->createQuery('delete from Warehouse\Entity\ShoppingList');
                    $q->execute();
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
     * Export the shopping list in an xls file
     */
    public function exportxlsAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $this->CreateXLSShoppingList();

                $file = '<a href="#" onclick="OpenRLink(\'../../../../shoppinglist/ShoppingList.xls\');">ShoppingList.xls</a>';
                $response = $this->getResponse();
                $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
                $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" => '', "file" => $file)));
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

    private function CreateXLSShoppingList(){
        $fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/shoppinglist/ShoppingList.xls', 'w');

        $shoppinglist = $this->getEntityManager()->getRepository('Warehouse\Entity\ShoppingList')->findByAll();
        $list = array();
        array_push($list,[
            'Recipe',
            'Article',
            'Quantity',
            'Section',
            'Supplier',
            'Area'
        ]);
        foreach ($shoppinglist as $sl) {
            if (!is_null($sl->getRecipe()))
                array_push($list,[
                    $sl->getRecipe()->getDescription(),
                    $sl->getDescription(),
                    $sl->getQuantity().' '.$sl->getUnit()->getUnit(),
                    $sl->getSection()->getDescription(),
                    $sl->getSupplier()->getDescription(),
                    $sl->getArea()->getDescription(),
                ]);
            else
                array_push($list,[
                    '',
                    $sl->getDescription(),
                    $sl->getQuantity().' '.$sl->getUnit()->getUnit(),
                    $sl->getSection()->getDescription(),
                    $sl->getSupplier()->getDescription(),
                    $sl->getArea()->getDescription(),
                ]);
        };

        foreach ($list as $l) {
            fputcsv($fp, $l, "\t", '"');
        }

        fclose($fp);
    }

    private function CreateHTMLShoppingList(){
        $shoppinglist = $this->getEntityManager()->getRepository('Warehouse\Entity\ShoppingList')->findByAll();

        $fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/shoppinglist/ShoppingList.html', 'w');

        $html = '<h1>Shopping List - '.date('d/m/Y').'</h1>';
        fwrite($fp, $html);

        $html = '<div class="shoppinglistdata">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th align="left">Section</th>
                            <th align="left">Description</th>
                            <th align="left">Recipe</th>
                            <th align="left">Priority</th>
                            <th align="left">Quantity</th>
                            <th align="left">Unit</th>
                            <th align="left">Area</th>
                        </tr>
                    </thead>
                    <tbody>';
        fwrite($fp, $html);
        $section = '';
        foreach($shoppinglist as $sl) {
            $html = '';
            if ($section != '' and $section != $sl->getSection()->getId()){
                $html = $html . '<tr><td colspan="7"><hr></td></tr>';
                $section = $sl->getSection()->getId();
            }
            if ($section == '') $section = $sl->getSection()->getId();
            $html = $html . '<tr>';
            $html = $html . '<td>' . $sl->getSection()->getDescription() . '</td>';
            $html = $html . '<td>' . $sl->getDescription() . '</td>';
            if (!is_null($sl->getRecipe())) {
                $html = $html . '<td>' . $sl->getRecipe()->getDescription() . '</td>';
            } else {
                $html = $html . '<td></td>';
            }
            if ($sl->getPriority() == 1) {
                $html = $html . '<td align="center">✓</td>';
            } else {
                $html = $html . '<td></td>';
            }
            $html = $html . '<td align="center">'  . $sl->getQuantity() . '</td>';
            $html = $html . '<td>' . $sl->getUnit()->getDescription() . '</td>';
            $html = $html . '<td>' . $sl->getArea()->getDescription() . '</td>';
            $html = $html . '</tr> ';
            fwrite($fp, $html);
        }
        $html = '</tbody></table></div>';
        fwrite($fp, $html);
        fclose($fp);
    }

    public function sendmailAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                // if (!file_exists($_SERVER["DOCUMENT_ROOT"] . '/shoppinglist/ShoppingList.xls'))
                $this->CreateXLSShoppingList();
                $this->CreateHTMLShoppingList();

                //send the mail using the service
                $mail = $this->getMailService();

                $arrayHtml = array();

                $attachment = new \Zend\Mime\Part(fopen($_SERVER["DOCUMENT_ROOT"] . '/shoppinglist/ShoppingList.html', 'r'));
                $attachment->type     = 'text/html';
                $attachment->charset  = 'utf-8';
                $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
                $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_INLINE;
                $attachment->filename = 'ShoppingList.html';
                array_push($arrayHtml,$attachment);

                $attachment = new \Zend\Mime\Part(fopen($_SERVER["DOCUMENT_ROOT"] . '/shoppinglist/ShoppingList.xls', 'r'));
                $attachment->type     = 'application/vnd.ms-excel';
                $attachment->charset  = 'utf-8';
                $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
                $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
                $attachment->filename = 'ShoppingList.xls';
                array_push($arrayHtml,$attachment);

                $mail->sendMail('','Shopping List - '.date('d/m/Y'),'',$arrayHtml);

                $this->redirect()->toRoute('warehouse/default', ['controller' => 'shopping', 'action' => 'list']);

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

    /**
     * Send the shopping list to the stock interface
     */
    public function sendtostockinterfaceAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $shoplist = $this->getEntityManager()->getRepository('Warehouse\Entity\ShoppingList')->findByAllSendToStock();
                foreach ($shoplist as $sp) {
                    $stockInterface = new StockInterface();
                    $stockInterface->setDescription($sp->getDescription());
                    $stockInterface->setMerge($sp->getStockmergement());
                    $stockInterface->setSens(EnumStockMovementType::MOVEMENT_SHOP_ADD);
                    $stockInterface->setQuantity($sp->getQuantity());
                    $stockInterface->setUnit($sp->getUnit());

                    $stockPrefered = $this->getEntityManager()->getRepository('Warehouse\Entity\Stock')->findByPreferedMergeId($sp->getStockmergement()->getId());
                    $stockInterface->setStock($stockPrefered[0]);

                    $qtyU = 0;
                    switch ($sp->getUnit()->getId()) {
                        case EnumUnit::UNIT_GRAM:
                            $qtyU = $sp->getQuantity();
                            break;
                        case EnumUnit::UNIT_PIECE:
                            $qtyU = $sp->getQuantity() * $sp->getStockmergement()->getEqpiece();
                            break;
                        case EnumUnit::UNIT_TABLESPOON:
                            $qtyU = $sp->getQuantity() * $sp->getStockmergement()->getEqtblsp();
                            break;
                        case EnumUnit::UNIT_COFFEESPOON:
                            $qtyU = $sp->getQuantity() * $sp->getStockmergement()->getEqcofsp();
                            break;
                        case EnumUnit::UNIT_TEASPOON:
                            $qtyU = $sp->getQuantity() * $sp->getStockmergement()->getEqteasp();
                            break;
                        case EnumUnit::UNIT_PINCH:
                            $qtyU = $sp->getQuantity() * $sp->getStockmergement()->getEqpinch();
                            break;
                        case EnumUnit::UNIT_MILLILITER:
                            $qtyU = $sp->getQuantity() / $sp->getStockmergement()->getEqpiece();
                            break;
                    }

                    $unit = $this->getEntityManager()->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId(EnumUnit::UNIT_PIECE, EnumTableSettings::MEASUREUNIT);
                    $stockInterface->setUnittointegrate($unit[0]);

                    if ($sp->getUnit()->getId() == EnumUnit::UNIT_PIECE) {
                        $stockInterface->setQuantitytointegrate($sp->getQuantity());
                    } else {
                        if ($sp->getStockmergement()->getEqpiece() != 0) {
                            $qty = $qtyU / $sp->getStockmergement()->getEqpiece();
                            if ($qty < 1) $qty = 1;
                        } else $qty = $sp->getQuantity();
                        $stockInterface->setQuantitytointegrate($qty);
                    }

                    $this->getEntityManager()->persist($stockInterface);
                    $this->getEntityManager()->flush();
                }

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