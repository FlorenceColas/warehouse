<?php

namespace Warehouse\Controller;

use Warehouse\Entity\StockMergement;
use Warehouse\Enum\EnumTableSettings;
use Warehouse\Form\StockMergementForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;

class StockmergementController extends AbstractActionController
{
    const ALL                           = 0;
    const ON_STOCK                      = 1;
    const NOT_ON_STOCK                  = 2;
    const UNDER_INFO_THRESHOLD          = 3;
    const UNDER_CRITICAL_THRESHOLD      = 4;
    const ON_STOCK_REQUIRE_MANUAL_CHECK = 5;

    protected $adapter;
    protected $config;
    protected $doctrine;

    public function __construct(
        $doctrine,
        DbAdapter $adapter,
        array $config
    ) {
        $this->doctrine = $doctrine;
        $this->adapter  = $adapter;
        $this->config   = $config;
    }

    public function loadunitAction()
    {
        $merge = $this->params()->fromRoute('id');

        $unit = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($merge);
        $html = $unit[0]->getMeasureUnit()->getUnit();

        return new JsonModel([
            'response' => true,
            'unit'     => $html,
        ]);
    }

    public function listAction()
    {
        $stockSession = new Container($this->config['session_containers']['stockmergement_search']);

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['area'])) {
                if (isset($stockSession->stockmergement_search_area) and $stockSession->stockmergement_search_area != $data['area']) {
                    unset($stockSession->stockmergement_search_section);
                    unset($data['sections']);
                }
                $stockSession->stockmergement_search_area = $data['area'];
            }
            if (isset($data['availability'])) {
                $stockSession->stockmergement_search_availability = $data['availability'];
            }
            if (isset($data['status'])) {
                $stockSession->stockmergement_search_status = $data['status'];
            }
            if (isset($data['sections'])) {
                $stockSession->stockmergement_search_section = $data['sections'];
            } else {
                unset($stockSession->stockmergement_search_section);
            }
        }

        $sectionInSession = [];
        if (isset($stockSession->stockmergement_search_section)) {
            $sectionInSession = $stockSession->stockmergement_search_section;
        }

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'description',
            'id',
        ])
            ->from('area')
            ->order([
                'description' => 'ASC',
            ]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $areas = [];
        foreach ($result as $row) {
            $areas[$row['id']] = [
                'description' => $row['description'],
                'id'          => $row['id'],
            ];
        }

        $critArea = 1;
        if (isset($stockSession->stockmergement_search_area)) {
            $critArea = $stockSession->stockmergement_search_area;
        }

        $select = $sql->select();
        $select->columns([
            'description',
            'id',
        ])
            ->from('section')
            ->where([
                'area_id' => $critArea,
            ])
            ->order('description');

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $sections = [];
        foreach ($result as $row) {
            if (0 === count($sectionInSession)) {
                $checked = 1;
            } else {
                $checked = in_array($row['id'], $sectionInSession);
            }
            $sections[$row['id']] = [
                'checked'     => $checked,
                'description' => $row['description'],
                'id'          => $row['id'],
            ];
        }

        if ($this->getRequest()->isGet()) {
            $stock = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->getStockByCriterias([
                'sections' => $sectionInSession,
                'area'     => $critArea,
            ]);
        } else {
            $stock = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->getStockByCriterias($data);
        }

        return new ViewModel([
            'stock'        => json_encode($stock),
            'areas'        => json_encode($areas),
            'selectedArea' => json_encode($critArea),
            'sections'     => json_encode($sections),
        ]);
    }

    public function displayAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $stockEntity = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($id);
        $stock       = $stockEntity[0];
        $form        = new StockMergementForm($this->doctrine);

        $stockMergedEntity = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findByMergeId($id);

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
        }

        return new ViewModel([
            'stock' => $stock,
            'form' => $form,
            'stockmerged' => $stockMergedEntity,
        ]);
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 1);

        $request = $this->getRequest();

        $stocks = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($id);
        $stock = $stocks[0];

        $form = new StockMergementForm($this->doctrine);

        $form->get('stockmergement')->get('section_id')->setValueOptions($this->LoadSectionValues($stock->getArea()->getId()));
        $form->get('stockmergement')->get('area_id')->setValueOptions($this->LoadAreaValues());
        $form->get('stockmergement')->get('supplier_id')->setValueOptions($this->LoadSupplierValues());
        $form->get('stockmergement')->get('measureunit_id')->setValueOptions($this->LoadUnitValues());

        if ($stock->getSection()->getId() != null) {
            $form->get('stockmergement')->get('section_id')->setAttributes([
                    'value' => $stock->getSection()->getId()
                ]
            );
        }
        if ($stock->getArea()->getId() != null) {
            $form->get('stockmergement')->get('area_id')->setAttributes([
                    'value' => $stock->getArea()->getId()
                ]
            );
        }
        if ($stock->getSupplier()->getId() != null) {
            $form->get('stockmergement')->get('supplier_id')->setAttributes([
                    'value' => $stock->getSupplier()->getId()
                ]
            );
        }
        if ($stock->getMeasureUnit()->getId() != null) {
            $form->get('stockmergement')->get('measureunit_id')->setAttributes([
                    'value' => $stock->getMeasureUnit()->getId()
                ]
            );
        }

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

                    $unit = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($form->get('stockmergement')->get('measureunit_id')->getValue(),EnumTableSettings::MEASUREUNIT);
                    $stock->setMeasureUnit($unit[0]);

                    $area = $this->doctrine->getRepository('Warehouse\Entity\Area')->findBySettingId($form->get('stockmergement')->get('area_id')->getValue(),EnumTableSettings::AREA);
                    $stock->setArea($area[0]);

                    $section = $this->doctrine->getRepository('Warehouse\Entity\Section')->findBySettingId($form->get('stockmergement')->get('section_id')->getValue(),EnumTableSettings::SECTION);
                    $stock->setSection($section[0]);

                    $supplier = $this->doctrine->getRepository('Warehouse\Entity\Supplier')->findBySettingId($form->get('stockmergement')->get('supplier_id')->getValue(),EnumTableSettings::SUPPLIER);
                    $stock->setSupplier($supplier[0]);

                    $this->doctrine->persist($stock);
                    $this->doctrine->flush();

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
            }
        }

        return new ViewModel([
            'stock' => $stock,
            'form' => $form,
        ]);
    }

    public function addAction()
    {
        $stock = new StockMergement();
        $request = $this->getRequest();

        $form = new StockMergementForm($this->doctrine);

        $form->get('stockmergement')->get('section_id')->setValueOptions($this->LoadSectionValues(''));
        $form->get('stockmergement')->get('area_id')->setValueOptions($this->LoadAreaValues());
        $form->get('stockmergement')->get('supplier_id')->setValueOptions($this->LoadSupplierValues());
        $form->get('stockmergement')->get('measureunit_id')->setValueOptions($this->LoadUnitValues());

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

                    $unit = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($form->get('stockmergement')->get('measureunit_id')->getValue(),EnumTableSettings::MEASUREUNIT);
                    $stock->setMeasureUnit($unit[0]);

                    $area = $this->doctrine->getRepository('Warehouse\Entity\Area')->findBySettingId($form->get('stockmergement')->get('area_id')->getValue(),EnumTableSettings::AREA);
                    $stock->setArea($area[0]);

                    $section = $this->doctrine->getRepository('Warehouse\Entity\Section')->findBySettingId($form->get('stockmergement')->get('section_id')->getValue(),EnumTableSettings::SECTION);
                    $stock->setSection($section[0]);

                    $supplier = $this->doctrine->getRepository('Warehouse\Entity\Supplier')->findBySettingId($form->get('stockmergement')->get('supplier_id')->getValue(),EnumTableSettings::SUPPLIER);
                    $stock->setSupplier($supplier[0]);
                    $this->doctrine->persist($stock);
                    $this->doctrine->flush();

                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockmergement', 'action' => 'list']);
                    return $this->getResponse();
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

        return new ViewModel([
            'stock' => $stock,
            'form' => $form,
        ]);
    }

    /**
     * Return json code which contains a drop down list with the sections corresponding to the area in parameter
     */
    public function loadsectionvaluesAction()
    {
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
    }

    /**
     * Recalculate the stock quantity and return it
     */
    public function recalculateAction()
    {
        $id = $this->params()->fromRoute('id');
        $response = $this->getResponse();

        $stockQty = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findCountQuantityForMergeId($id);
        $quantity = $stockQty[0]['quantity'];

        $stockMergement = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($id);
        $stMerge = $stockMergement[0];
        $stMerge->setNetquantity($quantity);
        $this->doctrine->persist($stMerge);
        $this->doctrine->flush();

        $sectionHtml = '<h4>Stock: ';
        if ($stMerge->getNetquantity() > 0) {
            $sectionHtml = $sectionHtml . '<font color="green">'.$stMerge->getNetquantity().$stMerge->getMeasureUnit()->getUnit().'</font>';
        }
        else {
            $sectionHtml = $sectionHtml .  '<font color="red">'.$stMerge->getNetquantity().$stMerge->getMeasureUnit()->getUnit().'</font>';
        }
        $sectionHtml = $sectionHtml . '</h4>';

        $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'stockqty' => $sectionHtml)));
        return $response;
    }

    protected function LoadSectionValues($area){
        //load Section list
        if ($area !== ''){
            $unit = $this->doctrine->getRepository('Warehouse\Entity\Section')->findByAreaOrderDescription(EnumTableSettings::SECTION, $area);
        } else {
            $unit = $this->doctrine->getRepository('Warehouse\Entity\Section')->findAllOrderByDescription(EnumTableSettings::SECTION);
        }
        $options = array();
        foreach($unit as $st) {
            $options[$st->getId()] = $st->getDescription();
        }
        return $options;
    }

    protected function LoadAreaValues(){
        //load Area list
        $area = $this->doctrine->getRepository('Warehouse\Entity\Area')->findAllOrderByDescription(EnumTableSettings::AREA);
        $options = array();
        foreach($area as $st) {
            $options[$st->getId()] = $st->getDescription();
        }
        return $options;
    }

    protected function LoadSupplierValues(){
        //load Supplier list
        $supplier = $this->doctrine->getRepository('Warehouse\Entity\Supplier')->findAllOrderByDescription(EnumTableSettings::SUPPLIER);
        $options = array();
        foreach($supplier as $sp) {
            $options[$sp->getId()] = $sp->getDescription();
        }
        return $options;
    }

    protected function LoadUnitValues(){
        //load unit list
        $unit = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findAvailableStockUnit(EnumTableSettings::MEASUREUNIT);
        $options = array();
        foreach($unit as $st) {
            $options[$st->getId()] = $st->getDescription();
        }
        return $options;
    }
}
