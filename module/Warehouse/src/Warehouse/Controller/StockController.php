<?php
/**
 * User: FlorenceColas
 * Date: 04/02/16
 * Version: 1.00
 * StockController: manage Inventory menu displayed, with search criteria (area, availability, status, section, part
 * of description). It contains the following actions:
 *      - list: inventory list
 *      - edit: edit the article
 *      - display: display the article
 *      - add: add a new article
 *      - loadsectionvalues: return a drop down of the sections corresponding to the area in criteria (json)
 *      - barcode: get the barcode as image
 *      - shoppinglist: add the article in the shopping list
 *      - generatebarcode: return the next available barcode (free zone)
 *      - sectionvalues: return the criteria Section part, corresponding to the area in parameter (json)
 *      - exportxls: export all articles in the current area selection to an Excel file
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *  29/04/2017 - integrate session/authentication
 */

namespace Warehouse\Controller;

use Doctrine\ORM\EntityManager;
use Warehouse\Entity\ShoppingList;
use Warehouse\Enum\EnumAppSettingsReferences;
use Warehouse\Enum\EnumPriority;
use Warehouse\Enum\EnumSession;
use Warehouse\Enum\EnumShoppingListStatus;
use Warehouse\Enum\EnumStatus;
use Warehouse\Form\CriteriaStockForm;
use Warehouse\Form\StockForm;
use Warehouse\Entity\Stock;
use Warehouse\Model\CriteriaStock;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Barcode\Barcode;

class StockController extends AbstractActionController
{
    protected $entityManager;
    protected $uploadPath;
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

                //read page number from the route
                $page = $this->params()->fromRoute('page', 1);

                //read the session container INVENTORYSEARCH
                $inventorySession = new Container(EnumSession::INVENTORYSEARCH);

                //read config file for db connection
                $config = $this->getServiceLocator()->get('Config');
                $adapter = new Adapter($config['db']);
                $sql = new Sql($adapter);

                //query to read all existing section in stock table, and count associated article
                //use in Search criteria
                $select = $sql->select();
                $select->from('stock');
                $statement = $sql->prepareStatementForSqlObject($select);
                $results = $statement->execute();

                //add section values in an array
                $sectionValues = array();
                $sectionValuesChecked = array();
                //query to read all existing areas
                //use in Search criteria
                $select = $sql->select();
                $select->columns(
                    [
                        'id',
                        'description',
                    ]);
                $select->from('area');
                $statement = $sql->prepareStatementForSqlObject($select);
                $results = $statement->execute();

                //add section values in an array
                $areaValues = array();
                foreach ($results as $row) {
                    $areaValues[$row['id']] = $row['description'];
                }

                //new Search Stock criteria form implementation
                $formCriteriaStockForm = new CriteriaStockForm($sectionValues, $areaValues);

                //fill in default values in Criteria Stock form if area values exist
                if ($inventorySession->offsetExists(EnumSession::INVENTORYSEARCH_AREA)) {
                    //            $formCriteriaStockForm->get('area')->setValue($inventorySession->area);;
                }
                //fill in default values in Criteria Stock form if session values exist
                if ($inventorySession->offsetExists(EnumSession::INVENTORYSEARCH_SECTION)) {
                    //            $sectionValuesChecked = $inventorySession->section;
                }
                if ($inventorySession->offsetExists(EnumSession::INVENTORYSEARCH_STATUS)) {
                    $formCriteriaStockForm->get('status')->setValue($inventorySession->status);
                }
                if ($inventorySession->offsetExists(EnumSession::INVENTORYSEARCH_AVAILABILITY)) {
                    $formCriteriaStockForm->get('availability')->setValue($inventorySession->availability);
                }

                //populate checked value in section criteria
                $formCriteriaStockForm->populateValues([
                    'section' => $sectionValuesChecked,
                ]);

                //fill in action in the form
                $formCriteriaStockForm->setAttribute('action', $this->getRequest()->getUri()->__toString());

                //POST action
                if ($this->getRequest()->isPost()) {
                    //read data from form
                    $data = $this->params()->fromPost();

                    $criteriaStock = new CriteriaStock();
                    $formCriteriaStockForm->bind($criteriaStock);
                    $formCriteriaStockForm->setData($data);

                    //test form valid
                    if (!$formCriteriaStockForm->isValid()) {
                        var_dump('err:' . sizeof($formCriteriaStockForm->getMessages()));
                        foreach ($formCriteriaStockForm->getMessages('section') as $msgId => $msg) {
                            var_dump('Validation error:' . $msgId . '=>' . $msg);
                        }

                        var_dump('form not valid');
                    }

                    //store criteria in session InventorySearch
                    $inventorySession->status = $criteriaStock->getStatus();
                    $inventorySession->availability = $criteriaStock->getAvailability();
                    //           $inventorySession->section = $criteriaStock->getSection();
                    //           $inventorySession->area = $criteriaStock->getArea();

                    //call repository with pagination limit
                    $limit = 50;
                    $page = 1;
                    $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
                    $stock = $this->getEntityManager()->getRepository('Warehouse\Entity\Stock')->getPagedStock($offset, $limit, $criteriaStock);
                } //GET Action
                else {
                    //call repository with pagination limit
                    $limit = 50;
                    $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
                    $stock = $this->getEntityManager()->getRepository('Warehouse\Entity\Stock')->getPagedStock($offset, $limit, null);
                }

                //add data to the ViewModel
                $viewModel->setVariables([
                    'pagedStock' => $stock,
                    'page' => $page,
                    'criteriastockform' => $formCriteriaStockForm,
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
     * Return json code with the criteria Section part, corresponding to the area in parameter
     */
    public function critsectionvaluesAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $areaid = $this->params()->fromRoute('id', 0);

                //read config file for db connection
                $config = $this->getServiceLocator()->get('Config');
                $adapter = new Adapter($config['db']);
                $sql = new Sql($adapter);

                //query to read all existing section in stock table, and count associated article
                //use in Search criteria
                $select = $sql->select();
                $select->columns(
                    [
                        'section_id as section_id',
                        'description' => new Expression('section.description'),
                        'counter' => new Expression('COUNT(stock.id)'),
                    ]);
                $select->from('stock');
                $select->join('section', 'section.id = stock.section_id', array(), 'left');
                $select->where('section.area_id='.$areaid);
                $select->group('section_id');
                $statement = $sql->prepareStatementForSqlObject($select);
                $results = $statement->execute();

                //add section values in an array
                $sectionHtml = '';
                foreach ($results as $row) {
                    $sectionHtml = $sectionHtml . '<label><input type="checkbox" name="section[]" value="'.$row['section_id'].'" checked="checked">'.$row['description'].' ('.$row['counter'] .')</label>';
                }

                $response = $this->getResponse();

                $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'section' => $sectionHtml)));
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
     * Display action of the article in parameter
     */
    public function displayAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $em = $this->getEntityManager();
                $id = $this->params()->fromRoute('id', 0);

                $stockEntity = $em->getRepository('Warehouse\Entity\Stock')->findByStockId($id);
                $stock = $stockEntity[0];
                $form = new StockForm($em);

                $form->setBindOnValidate(false);
                $form->bind($stock);

                $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
                if ($this->request->isPost()) {
                    $data = $this->params()->fromPost();
                    if (isset($data['cancel']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stock', 'action' => 'list']);
                        return $this->getResponse();
                    }
                    if (isset($data['backToList']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stock', 'action' => 'list']);
                        return $this->getResponse();
                    }
                    if (isset($data['add']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stock', 'action' => 'add']);
                        return $this->getResponse();
                    }
                    if (isset($data['update']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stock', 'action' => 'edit', 'id' => $id]);
                        return $this->getResponse();
                    }
                    if (isset($data['delete']) == 1) {


                    }
                } else {
                }

                //read the default stock photo stored in attachment table
                $AttachmentEntity = $em->getRepository('Warehouse\Entity\Attachment')->findByStockIdDefaultPhoto($id);
                if ($AttachmentEntity) {
                    $Attachment = $AttachmentEntity[0];
                } else {
                    $Attachment = null;
                }
                $viewModel->setVariables([
                    'stock' => $stock,
                    'form' => $form,
                    'defaultphoto' => $Attachment,
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
     * Add a new article action
     */
    public function addAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();
                $em = $this->getEntityManager();

                $stock = new Stock();
                $request = $this->getRequest();

                $form = new StockForm($em);
                $form->setBindOnValidate(false);
                $form->bind($stock);

                $form->get('stock')->get('merge_id')->setValueOptions($this->LoadShortDescriptionValues());
                $form->get('stock')->get('quantity')->setValue(0);
                $form->get('stock')->get('netquantity')->setValue(0);
                $form->get('stock')->get('infothreshold')->setValue(0);
                $form->get('stock')->get('criticalthreshold')->setValue(0);

                $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
                if ($request->isPost()) {
                    $data = $this->params()->fromPost();
                    if (isset($data['cancel']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stock', 'action' => 'list']);
                        return $this->getResponse();
                    }
                    if (isset($data['update']) == 1) {
                        $form->setData($data);
                        if ($form->isValid()) {
                            $stock->setDescription($form->get('stock')->get('description')->getValue());
                            if ($form->get('stock')->get('merge_id')->getValue() != ''){
                                $merge = $em->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($form->get('stock')->get('merge_id')->getValue());
                                $stock->setMerge($merge[0]);
                            }
                            if ($form->get('stock')->get('chkautobarcode')->getValue() == 'auto') {
                                $stock->setBarcode($this->generatebarcodeAction());
                            } else {
                                $stock->setBarcode($form->get('stock')->get('barcode')->getValue());
                            }
                            $stock->setPrefered($form->get('stock')->get('prefered')->getValue());
                            $stock->setQuantity($form->get('stock')->get('quantity')->getValue());
                            $stock->setNetquantity($form->get('stock')->get('netquantity')->getValue());
                            $stock->setInfothreshold($form->get('stock')->get('infothreshold')->getValue());
                            $stock->setCriticalthreshold($form->get('stock')->get('criticalthreshold')->getValue());
                            $stock->setSupplierreference($form->get('stock')->get('supplierreference')->getValue());
                            $stock->setNotes($form->get('stock')->get('notes')->getValue());
                            $stock->setStatus($form->get('stock')->get('status')->getValue());
                            $em->persist($stock);
                            $em->flush();

                            if ($form->get('stock')->get('merge_id')->getValue() != '') {
                                //read the quantity in stock table
                                $stockQty = $em->getRepository('Warehouse\Entity\Stock')->findCountQuantityForMergeId($form->get('stock')->get('merge_id')->getValue());
                                $quantity = $stockQty[0]['quantity'];
                                //update stock quantity in StockMergement table
                                $merge[0]->setNetquantity(intval($quantity));
                                $em->persist($merge[0]);
                                $em->flush();
                            }

                            $this->redirect()->toRoute('warehouse/default', ['controller' => 'stock', 'action' => 'list']);
                            return $this->getResponse();
                        }
                        else {
                        }

                    }

                }

                //when the form is validated, labels on buttons not clicked are disappear
                //these step allows to add label on them
                foreach ($form->getElements() as $elt){
                    switch ($elt->getAttribute('name')) {
                        case 'update':
                            $elt->setValue('Update');
                            break;
                        case 'cancel':
                            $elt->setValue('Cancel');
                            break;
                    }
                }

                $viewModel->setVariables([
                    'stock' => $stock,
                    'form' => $form,
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
     * Edit action of the article in parameter
     */
    public function editAction() {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $id = $this->params()->fromRoute('id', 1);
                $em = $this->getEntityManager();

                $request = $this->getRequest();

                //read the recipe details
                $stocks = $em->getRepository('Warehouse\Entity\Stock')->findByStockId($id);
                $stock = $stocks[0];

                $form = new StockForm($em);

                $form->get('stock')->get('merge_id')->setValueOptions($this->LoadShortDescriptionValues());
                //load default values
                $form->get('stock')->get('status')->setAttributes([
                        'value' => $stock->getStatus()
                    ]
                );
                if ($stock->getMerge() != null)
                    $form->get('stock')->get('merge_id')->setAttributes([
                            'value' => $stock->getMerge()->getId()
                        ]
                    );

                $form->setBindOnValidate(false);
                $form->bind($stock);

                $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
                if ($request->isPost()) {
                    $data = $this->params()->fromPost();
                    if (isset($data['backToList']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stock', 'action' => 'list']);
                        return $this->getResponse();
                    }
                    if (isset($data['cancel']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stock', 'action' => 'display', 'id' => $id]);
                        return $this->getResponse();
                    }
                    if (isset($data['update']) == 1) {
                        $form->setData($data);
                        if ($form->isValid()) {
                            $stock->setDescription($form->get('stock')->get('description')->getValue());
                            if ($form->get('stock')->get('merge_id')->getValue() != '') {
                                $merge = $em->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($form->get('stock')->get('merge_id')->getValue());
                                $stock->setMerge($merge[0]);
                            }
                            if ($form->get('stock')->get('chkautobarcode')->getValue() == 'auto') {
                                $stock->setBarcode($this->generatebarcodeAction());
                            } else {
                                $stock->setBarcode($form->get('stock')->get('barcode')->getValue());
                            }
                            $stock->setPrefered($form->get('stock')->get('prefered')->getValue());
                            $stock->setQuantity($form->get('stock')->get('quantity')->getValue());
                            $stock->setNetquantity($form->get('stock')->get('netquantity')->getValue());
                            $stock->setInfothreshold($form->get('stock')->get('infothreshold')->getValue());
                            $stock->setCriticalthreshold($form->get('stock')->get('criticalthreshold')->getValue());
                            $stock->setSupplierReference($form->get('stock')->get('supplierreference')->getValue());
                            $stock->setNotes($form->get('stock')->get('notes')->getValue());
                            $stock->setStatus($form->get('stock')->get('status')->getValue());
                            $em->persist($stock);
                            $em->flush();

                            if ($form->get('stock')->get('merge_id')->getValue() != '') {
                                //only one prefered article per merge_id
                                if ($form->get('stock')->get('prefered')->getValue() == '1'){
                                    $merged = $em->getRepository('Warehouse\Entity\Stock')->findByAllMergeIdExceptCurrent($form->get('stock')->get('merge_id')->getValue(),$form->get('stock')->get('id')->getValue());
                                    foreach ($merged as $m){
                                        $m->setPrefered(99);
                                        $em->persist($m);
                                        $em->flush();
                                    }
                                }

                                //read the quantity in stock table
                                $stockQty = $em->getRepository('Warehouse\Entity\Stock')->findCountQuantityForMergeId($form->get('stock')->get('merge_id')->getValue());
                                $quantity = $stockQty[0]['quantity'];
                                //update stock quantity in StockMergement table
                                $merge[0]->setNetquantity(intval($quantity));
                                $em->persist($merge[0]);
                                $em->flush();
                            }

                            //when the form is validated, labels on buttons not clicked are disappear
                            //these step allows to add label on them
                            foreach ($form->getElements() as $elt) {
                                switch ($elt->getAttribute('name')) {
                                    case 'update':
                                        $elt->setValue('Update');
                                        break;
                                    case 'cancel':
                                        $elt->setValue('Cancel');
                                        break;
                                }
                            }
                            $this->redirect()->toRoute('warehouse/default', ['controller' => 'stock', 'action' => 'display', 'id' => $id]);
                            return $this->getResponse();
                        }
                        else {
                            $messages = $form->getMessages();
                            $viewModel->setVariables([
                                'messages' => $messages,
                            ]);
                        }
                    }
                }

                $viewModel->setVariables([
                    'stock' => $stock,
                    'form' => $form,
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
     * Return json code which contains a drop down list with the sections corresponding to the area in parameter
     */
    public function loadsectionvaluesAction() {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $area = $this->params()->fromRoute('id');
                $response = $this->getResponse();

                $values = $this->LoadSectionValues($area);
                $sectionHtml = '<p>Section: <select name="stock[section_id]">
                    <option value="">Please choose the section</option>';

                foreach ($values as $key => $value) {
                    $sectionHtml = $sectionHtml . '<option value="' . $key . '">' . $value . '</option>';
                }
                $sectionHtml = $sectionHtml . '</select></p></div>';
                $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'section' => $sectionHtml)));
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
     * Return the barcode like a barcode image (displayed in the article details)
     */
    public function barcodeAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                //read barcode from route parameters
                $id = $this->params()->fromRoute('id', 0);
                $barcodeOptions = array('text' => substr($id, 0, 12));
                $rendererOptions = array();
                return Barcode::factory('ean13', 'image', $barcodeOptions, $rendererOptions)->render();
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
     * From the article list, add 1 quantity of the article to the shopping list table
     */
    public function shoppinglistAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $em = $this->getEntityManager();
                $id = $this->params()->fromRoute('id', 0);

                $stockEntity = $em->getRepository('Warehouse\Entity\Stock')->findByStockId($id);
                $stock = $stockEntity[0];

                $stockMergementEntity = $em->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($stock->getMerge()->getId());
                $stockMergement = $stockMergementEntity[0];

                $shopStock = $em->getRepository('Warehouse\Entity\ShoppingList')->findByStockMergementId($stockMergement->getId());
                if (isset($shopStock) and $shopStock!= null and count($shopStock[0]) <> 0) {
                    $shoppingList = $shopStock[0];
                    $quantity = $shopStock[0]->getQuantity() + 1;
                } else {
                    $shoppingList = new ShoppingList();
                    $quantity = 1;
                    $shoppingList->setDescription($stockMergement->getDescription());
                    $section = $em->getRepository('Warehouse\Entity\Section')->findBySettingId($stockMergement->getSection()->getId(),'section');
                    $shoppingList->setSection($section[0]);
                    $supplier = $em->getRepository('Warehouse\Entity\Supplier')->findBySettingId($stockMergement->getSupplier()->getId(),'supplier');
                    $shoppingList->setSupplier($supplier[0]);
                    $shoppingList->setPriority(EnumPriority::PRIORITY_MAJOR);
                    $unit = $em->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId(1,'measureunit');
                    $shoppingList->setStockmergement($stockMergement);
                    $shoppingList->setUnit($unit[0]);
                    $area = $em->getRepository('Warehouse\Entity\Area')->findBySettingId($stockMergement->getArea()->getId(),'area');
                    $shoppingList->setArea($area[0]);
                    $shoppingList->setRecipe(null);
                }
                $shoppingList->setSendtostock(1);
                $shoppingList->setQuantity($quantity);
                $shoppingList->setStatus(EnumShoppingListStatus::SHOPPING_LIST_STATUS_NEW_TO_BUY);

                $em->persist($shoppingList);
                $em->flush();

                $this->redirect()->toRoute('warehouse/default', ['controller' => 'stock', 'action' => 'list']);
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
     * Return the next available "free" barcode value
     * @return string
     */
    public function generatebarcodeAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                //read the last barcode value generated
                $lastBC = $this->getEntityManager()->getRepository('Warehouse\Entity\Appsettings')->findByReference(EnumAppSettingsReferences::APP_SETTINGS_LAST_BARCODE_AUTO_GENERATED)->getResult();
                $lastGeneratedBarCode = $lastBC[0]->getSettingvalue() + 1;

                //calculated the EAN13 barcode value
                $arrBC = str_split($lastGeneratedBarCode);
                //add odd numbers with weight factor = 1
                $odd = 0;
                for ($i=0;$i<11;$i = $i+2){
                    $odd = $odd + $arrBC[$i];
                }
                //add even numbers with weight factor = 3
                $even = 0;
                for ($i=1;$i<12;$i = $i+2){
                    $even = $even + ($arrBC[$i] * 3);
                }
                //add odd and even total
                $total = $odd + $even;
                //calculate the rest of the division by 10
                $rest = $total % 10;
                //if the rest is equals to 0, the barcode key is 0
                if ($rest == 0) $newBarCode = $lastGeneratedBarCode . $rest;
                else  {
                    $key = 10 - $rest;
                    $newBarCode = $lastGeneratedBarCode . $key;
                }
                $lastBC[0]->setSettingValue(strval($lastGeneratedBarCode));
                $this->getEntityManager()->persist($lastBC[0]);
                return $newBarCode;
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
     * Return an array of the existing short description
     * @return array
     */
    private function LoadShortDescriptionValues() {
        //load Area list
        $area = $this->getEntityManager()->getRepository('Warehouse\Entity\StockMergement')->findAllOrderByDescription();
        $options = array();
        foreach($area as $st) {
            $options[$st->getId()] = $st->getDescription();
        }
        return $options;
    }

    /**
     * Export the inventory list in an xls file
     */
    public function exportxlsAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/inventory/Inventory.xls', 'w');

                //read the session container INVENTORYSEARCH
                $inventorySession = new Container(EnumSession::INVENTORYSEARCH);

                $stock = $this->getEntityManager()->getRepository('Warehouse\Entity\Stock')->findAllOrderByDescription();
                $list = array();
                array_push($list,[
                    'Id',
                    'Barcode',
                    'Description',
                    'Quantity',
                    'status',
                ]);

                foreach ($stock as $sl) {
                    $status = '';
                    switch ($sl->getStatus() == EnumStatus::Enabled) {
                        case EnumStatus::Enabled:
                            $status = 'Enabled';
                            break;
                        case EnumStatus::Disabled:
                            $status = 'Disabled';
                            break;
                        case EnumStatus::Blocked:
                            $status = 'Blocked';
                            break;
                    }
                    array_push($list,[
                        $sl->getId(),
                        $sl->getBarcode(),
                        $sl->getDescription(),
                        $sl->getQuantity(),
                        $status,
                    ]);
                };

                foreach ($list as $l) {
                    fputcsv($fp, $l, "\t", '"');
                }

                fclose($fp);

                $file = '<a href="#" onclick="OpenRLink(\'../../../../inventory/Inventory.xls\');">Inventory.xls</a>';
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
}

