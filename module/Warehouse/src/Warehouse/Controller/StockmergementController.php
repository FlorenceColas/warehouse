<?php
/**
 * User: FlorenceColas
 * Date: 24/01/2017
 * Version: 1.00
 * StockMergementController: manage StockMergement functionality. It contains the following actions:
 *      - loadunit: return the unit for the id in parameter
 *      - list: stock list
 *      - edit: edit the stock
 *      - display: display the stock
 *      - add: add a new stock
 *      - loadsectionvalues: return json code which contains a drop down list with the sections corresponding to the area in parameter
 *      - recalculate: recalculate the stock quantity and return json with it
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *  29/04/2017 - integrate session/authentication
 */

namespace Warehouse\Controller;

use Doctrine\ORM\EntityManager;
use Warehouse\Entity\StockMergement;
use Warehouse\Enum\EnumSession;
use Warehouse\Enum\EnumTableSettings;
use Warehouse\Form\CriteriaStockMergementForm;
use Warehouse\Form\StockMergementForm;
use Warehouse\Model\CriteriaStockMergement;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class StockmergementController extends AbstractActionController
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

    public function loadunitAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $merge = $this->params()->fromRoute('id');
                $response = $this->getResponse();

                $unit = $this->getEntityManager()->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($merge);
                $html = $unit[0]->getUnit()->getUnit();

                $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'unit' => $html)));
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

    /*
     * Display the stock (list action)
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
                $select->columns(
                    [
                        'section_id as section_id',
                        'description' => new Expression('section.description'),
                        'counter' => new Expression('COUNT(stockmergement.id)'),
                    ]);
                $select->from('stockmergement');
                $select->join('section', 'section.id = stockmergement.section_id', array(), 'left');
                if ($inventorySession->offsetExists(EnumSession::INVENTORYSEARCH_AREA)) {
                    $select->where('section.area_id='.$inventorySession->area);
                } else {
                    $select->where('section.area_id=1');
                }
                $select->group('section_id');
                $statement = $sql->prepareStatementForSqlObject($select);
                $results = $statement->execute();

                //add section values in an array
                $sectionValues = array();
                $sectionValuesChecked = array();
                foreach ($results as $row) {
                    $sectionValues[$row['section_id']] = $row['description'].' ('.$row['counter'] .')';
                    //if there is no session value, the criteria is checked by default
                    if (!$inventorySession->offsetExists(EnumSession::INVENTORYSEARCH_SECTION)) {
                        $sectionValuesChecked[] = $row['section_id'] . '';
                    }
                }

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
                $formCriteriaStockForm = new CriteriaStockMergementForm($sectionValues, $areaValues);

                //fill in default values in Criteria Stock form if area values exist
                if ($inventorySession->offsetExists(EnumSession::INVENTORYSEARCH_AREA)) {
                    $formCriteriaStockForm->get('area')->setValue($inventorySession->area);;
                }
                //fill in default values in Criteria Stock form if session values exist
                if ($inventorySession->offsetExists(EnumSession::INVENTORYSEARCH_SECTION)) {
                    $sectionValuesChecked = $inventorySession->section;
                }

                //populate checked value in section criteria
                $formCriteriaStockForm->populateValues([
                    'section' => $sectionValuesChecked,
                ]);

                //fill in action in the form
                $formCriteriaStockForm->setAttribute('action' ,$this->getRequest()->getUri()->__toString());

                //POST action
                if ($this->getRequest()->isPost()) {
                    //read data from form
                    $data = $this->params()->fromPost();

                    $criteriaStock = new CriteriaStockMergement();
                    $formCriteriaStockForm->bind($criteriaStock);
                    $formCriteriaStockForm->setData($data);

                    //test form valid
                    if (!$formCriteriaStockForm->isValid())
                    {
                        var_dump('err:'.sizeof($formCriteriaStockForm->getMessages()));
                        foreach($formCriteriaStockForm->getMessages('section') as $msgId => $msg) {
                            var_dump('Validation error:'. $msgId. '=>'. $msg);
                        }

                        var_dump('form not valid');
                    }

                    //store criteria in session InventorySearch
                    $inventorySession->status = $criteriaStock->getStatus();
                    $inventorySession->availability = $criteriaStock->getAvailability();
                    $inventorySession->section = $criteriaStock->getSection();
                    $inventorySession->area = $criteriaStock->getArea();

                    //call repository with pagination limit
                    $limit = 50;
                    $page = 1;
                    $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
                    $stock = $this->getEntityManager()->getRepository('Warehouse\Entity\StockMergement')->getPagedStock($offset, $limit, $criteriaStock);
                }
                //GET Action
                else {
                    //call repository with pagination limit
                    $limit = 50;
                    $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
                    $stock = $this->getEntityManager()->getRepository('Warehouse\Entity\StockMergement')->getPagedStock($offset, $limit, null);
                }

                //add data to the ViewModel
                $viewModel->setVariables([
                    'pagedStock' => $stock,
                    'page' => $page,
                    'criteriastockmergementform' => $formCriteriaStockForm,
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
     * Display action of the stock in parameter
     */
    public function displayAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $em = $this->getEntityManager();
                $id = $this->params()->fromRoute('id', 0);

                $stockEntity = $em->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($id);
                $stock = $stockEntity[0];
                $form = new StockMergementForm($em);

                $stockMergedEntity = $em->getRepository('Warehouse\Entity\Stock')->findByMergeId($id);

                $form->setBindOnValidate(false);
                $form->bind($stock);

                $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
                if ($this->request->isPost()) {
                    $data = $this->params()->fromPost();
                    if (isset($data['cancel']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockmergement', 'action' => 'list']);
                        return $this->getResponse();
                    }
                    if (isset($data['backToList']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockmergement', 'action' => 'list']);
                        return $this->getResponse();
                    }
                    if (isset($data['add']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockmergement', 'action' => 'add']);
                        return $this->getResponse();
                    }
                    if (isset($data['update']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockmergement', 'action' => 'edit', 'id' => $id]);
                        return $this->getResponse();
                    }
                    if (isset($data['delete']) == 1) {


                    }
                } else {
                }

                $viewModel->setVariables([
                    'stock' => $stock,
                    'form' => $form,
                    'stockmerged' => $stockMergedEntity,
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
     * Edit action of the stock in parameter
     */
    public function editAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $id = $this->params()->fromRoute('id', 1);
                $em = $this->getEntityManager();

                $request = $this->getRequest();

                //read the recipe details
                $stocks = $em->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($id);
                $stock = $stocks[0];

                $form = new StockMergementForm($em);

                $form->get('stockmergement')->get('section_id')->setValueOptions($this->LoadSectionValues($stock->getArea()->getId()));
                $form->get('stockmergement')->get('area_id')->setValueOptions($this->LoadAreaValues());
                $form->get('stockmergement')->get('supplier_id')->setValueOptions($this->LoadSupplierValues());
                $form->get('stockmergement')->get('unit_id')->setValueOptions($this->LoadUnitValues());

                $form->get('stockmergement')->get('section_id')->setAttributes([
                        'value' => $stock->getSection()->getId()
                    ]
                );
                $form->get('stockmergement')->get('area_id')->setAttributes([
                        'value' => $stock->getArea()->getId()
                    ]
                );
                $form->get('stockmergement')->get('supplier_id')->setAttributes([
                        'value' => $stock->getSupplier()->getId()
                    ]
                );
                $form->get('stockmergement')->get('unit_id')->setAttributes([
                        'value' => $stock->getUnit()->getId()
                    ]
                );

                $form->setBindOnValidate(false);
                $form->bind($stock);

                $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
                if ($request->isPost()) {
                    $data = $this->params()->fromPost();
                    if (isset($data['backToList']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockmergement', 'action' => 'list']);
                        return $this->getResponse();
                    }
                    if (isset($data['cancel']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockmergement', 'action' => 'display', 'id' => $id]);
                        return $this->getResponse();
                    }
                    if (isset($data['update']) == 1) {
                        $form->setData($data);
                        if ($form->isValid()) {
                            $stock->setDescription($form->get('stockmergement')->get('description')->getValue());
                            $stock->setEqtblsp($form->get('stockmergement')->get('eqtblsp')->getValue());
                            $stock->setEqcofsp($form->get('stockmergement')->get('eqcofsp')->getValue());
                            $stock->setEqteasp($form->get('stockmergement')->get('eqteasp')->getValue());
                            $stock->setEqpinch($form->get('stockmergement')->get('eqpinch')->getValue());
                            $stock->setEqpiece($form->get('stockmergement')->get('eqpiece')->getValue());

                            $unit = $em->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($form->get('stockmergement')->get('unit_id')->getValue(),EnumTableSettings::MEASUREUNIT);
                            $stock->setUnit($unit[0]);

                            $area = $em->getRepository('Warehouse\Entity\Area')->findBySettingId($form->get('stockmergement')->get('area_id')->getValue(),EnumTableSettings::AREA);
                            $stock->setArea($area[0]);

                            $section = $em->getRepository('Warehouse\Entity\Section')->findBySettingId($form->get('stockmergement')->get('section_id')->getValue(),EnumTableSettings::SECTION);
                            $stock->setSection($section[0]);

                            $supplier = $em->getRepository('Warehouse\Entity\Supplier')->findBySettingId($form->get('stockmergement')->get('supplier_id')->getValue(),EnumTableSettings::SUPPLIER);
                            $stock->setSupplier($supplier[0]);

                            $em->persist($stock);
                            $em->flush();

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
                            $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockmergement', 'action' => 'display', 'id' => $id]);
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
     * Add a new stock action
     */
    public function addAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();
                $em = $this->getEntityManager();

                $stock = new StockMergement();
                $request = $this->getRequest();

                $form = new StockMergementForm($em);

                $form->get('stockmergement')->get('section_id')->setValueOptions($this->LoadSectionValues(''));
                $form->get('stockmergement')->get('area_id')->setValueOptions($this->LoadAreaValues());
                $form->get('stockmergement')->get('supplier_id')->setValueOptions($this->LoadSupplierValues());
                $form->get('stockmergement')->get('unit_id')->setValueOptions($this->LoadUnitValues());

                $form->setBindOnValidate(false);
                $form->bind($stock);

                $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
                if ($request->isPost()) {
                    $data = $this->params()->fromPost();
                    if (isset($data['cancel']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockmergement', 'action' => 'list']);
                        return $this->getResponse();
                    }
                    if (isset($data['update']) == 1) {
                        $form->setData($data);
                        if ($form->isValid()) {
                            $stock->setDescription($form->get('stockmergement')->get('description')->getValue());
                            $stock->setEqtblsp($form->get('stockmergement')->get('eqtblsp')->getValue());
                            $stock->setEqcofsp($form->get('stockmergement')->get('eqcofsp')->getValue());
                            $stock->setEqteasp($form->get('stockmergement')->get('eqteasp')->getValue());
                            $stock->setEqpinch($form->get('stockmergement')->get('eqpinch')->getValue());
                            $stock->setEqpiece($form->get('stockmergement')->get('eqpiece')->getValue());
                            $stock->setNetquantity(0);

                            $unit = $em->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($form->get('stockmergement')->get('unit_id')->getValue(),EnumTableSettings::MEASUREUNIT);
                            $stock->setUnit($unit[0]);

                            $area = $em->getRepository('Warehouse\Entity\Area')->findBySettingId($form->get('stockmergement')->get('area_id')->getValue(),EnumTableSettings::AREA);
                            $stock->setArea($area[0]);

                            $section = $em->getRepository('Warehouse\Entity\Section')->findBySettingId($form->get('stockmergement')->get('section_id')->getValue(),EnumTableSettings::SECTION);
                            $stock->setSection($section[0]);

                            $supplier = $em->getRepository('Warehouse\Entity\Supplier')->findBySettingId($form->get('stockmergement')->get('supplier_id')->getValue(),EnumTableSettings::SUPPLIER);
                            $stock->setSupplier($supplier[0]);
                            $em->persist($stock);
                            $em->flush();

                            $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockmergement', 'action' => 'list']);
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
     * Return json code which contains a drop down list with the sections corresponding to the area in parameter
     */
    public function loadsectionvaluesAction()
    {
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
     * Recalculate the stock quantity and return it
     */
    public function recalculateAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $id = $this->params()->fromRoute('id');
                $response = $this->getResponse();

                $em = $this->getEntityManager();
                $stockQty = $em->getRepository('Warehouse\Entity\Stock')->findCountQuantityForMergeId($id);
                $quantity = $stockQty[0]['quantity'];

                $stockMergement = $em->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($id);
                $stMerge = $stockMergement[0];
                $stMerge->setNetquantity($quantity);
                $em->persist($stMerge);
                $em->flush();

                $sectionHtml = '<h4>Stock: ';
                if ($stMerge->getNetquantity() > 0) {
                    $sectionHtml = $sectionHtml . '<font color="green">'.$stMerge->getNetquantity().$stMerge->getUnit()->getUnit().'</font>';
                }
                else {
                    $sectionHtml = $sectionHtml .  '<font color="red">'.$stMerge->getNetquantity().$stMerge->getUnit()->getUnit().'</font>';
                }
                $sectionHtml = $sectionHtml . '</h4>';

                $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'stockqty' => $sectionHtml)));
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

    private function LoadSectionValues($area){
        //load Section list
        if ($area !== ''){
            $unit = $this->getEntityManager()->getRepository('Warehouse\Entity\Section')->findByAreaOrderDescription(EnumTableSettings::SECTION, $area);
        } else {
            $unit = $this->getEntityManager()->getRepository('Warehouse\Entity\Section')->findAllOrderByDescription(EnumTableSettings::SECTION);
        }
        $options = array();
        foreach($unit as $st) {
            $options[$st->getId()] = $st->getDescription();
        }
        return $options;
    }

    private function LoadAreaValues(){
        //load Area list
        $area = $this->getEntityManager()->getRepository('Warehouse\Entity\Area')->findAllOrderByDescription(EnumTableSettings::AREA);
        $options = array();
        foreach($area as $st) {
            $options[$st->getId()] = $st->getDescription();
        }
        return $options;
    }

    private function LoadSupplierValues(){
        //load Supplier list
        $supplier = $this->getEntityManager()->getRepository('Warehouse\Entity\Supplier')->findAllOrderByDescription(EnumTableSettings::SUPPLIER);
        $options = array();
        foreach($supplier as $sp) {
            $options[$sp->getId()] = $sp->getDescription();
        }
        return $options;
    }

    private function LoadUnitValues(){
        //load unit list
        $unit = $this->getEntityManager()->getRepository('Warehouse\Entity\MeasureUnit')->findAvailableStockUnit(EnumTableSettings::MEASUREUNIT);
        $options = array();
        foreach($unit as $st) {
            $options[$st->getId()] = $st->getDescription();
        }
        return $options;
    }
}