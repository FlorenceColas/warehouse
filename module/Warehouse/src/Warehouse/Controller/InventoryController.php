<?php

namespace Warehouse\Controller;

use Warehouse\Entity\ShoppingList;
use Warehouse\Form\StockForm;
use Warehouse\Entity\Stock;
use Zend\Barcode\Barcode;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class InventoryController extends AbstractActionController
{
    const DISABLED = 0;
    const ENABLED  = 1;
    const BLOCKED  = 2;

    protected $adapter;
    protected $config;
    protected $doctrine;

    public function __construct(
        array $config,
        $doctrine,
        DbAdapter $adapter
    ) {
        $this->config   = $config;
        $this->doctrine = $doctrine;
        $this->adapter  = $adapter;
    }

    public function listAction()
    {
        $inventorySession = new Container($this->config['session_containers']['inventory_search']);

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['area'])) {
                if (isset($inventorySession->inventory_search_area) and $inventorySession->inventory_search_area != $data['area']) {
                    unset($inventorySession->inventory_search_section);
                    unset($data['sections']);
                }
                $inventorySession->inventory_search_area = $data['area'];
            }
            if (isset($data['availability'])) {
                $inventorySession->inventory_search_availability = $data['availability'];
            }
            if (isset($data['status'])) {
                $inventorySession->inventory_search_status = $data['status'];
            }
            if (isset($data['sections'])) {
                $inventorySession->inventory_search_section = $data['sections'];
            } else {
                unset($inventorySession->inventory_search_section);
            }
        }

        $sectionInSession = [];
        if (isset($inventorySession->inventory_search_section)) {
            $sectionInSession = $inventorySession->inventory_search_section;
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
        if (isset($inventorySession->inventory_search_area)) {
            $critArea = $inventorySession->inventory_search_area;
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
            $stock = $this->doctrine->getRepository('Warehouse\Entity\Stock')->getStockByCriterias([
                'sections' => $sectionInSession,
                'area'     => $critArea,
            ]);
        } else {
            $stock = $this->doctrine->getRepository('Warehouse\Entity\Stock')->getStockByCriterias($data);
        }

        return new ViewModel([
            'stock'        => json_encode($stock),
            'areas'        => json_encode($areas),
            'selectedArea' => json_encode($critArea),
            'urlthumb'     => json_encode($this->config['url']['stock_thumb']),
            'sections'     => json_encode($sections),
        ]);
    }

    public function displayAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $stockEntity = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findByStockId($id);
        $stock = $stockEntity[0];
        $form = new StockForm($this->doctrine);

        $form->setBindOnValidate(false);
        $form->bind($stock);

        $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
        if ($this->request->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['cancel']) == 1) {
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'inventory', 'action' => 'list']);
                return $this->getResponse();
            }
            if (isset($data['backToList']) == 1) {
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'inventory', 'action' => 'list']);
                return $this->getResponse();
            }
            if (isset($data['add']) == 1) {
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'inventory', 'action' => 'add']);
                return $this->getResponse();
            }
            if (isset($data['update']) == 1) {
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'inventory', 'action' => 'edit', 'id' => $id]);
                return $this->getResponse();
            }
            if (isset($data['delete']) == 1) {
            }
        }

        $attachmentEntity = $this->doctrine->getRepository('Warehouse\Entity\StockAttachment')->findByStockIdDefaultPhoto($id);
        if ($attachmentEntity) {
            $attachment = $attachmentEntity[0];
        } else {
            $attachment = null;
        }

        return new ViewModel([
            'stock'        => $stock,
            'form'         => $form,
            'urlthumb'     => $this->config['url']['stock_thumb'],
            'defaultphoto' => $attachment,
        ]);
    }

    public function addAction()
    {
        $stock = new Stock();
        $request = $this->getRequest();

        $form = new StockForm($this->doctrine);
        $form->setBindOnValidate(false);
        $form->bind($stock);

        $form->get('stock')->get('stockmergement_id')->setValueOptions($this->LoadShortDescriptionValues());
        $form->get('stock')->get('quantity')->setValue(0);
        $form->get('stock')->get('netquantity')->setValue(0);
        $form->get('stock')->get('infothreshold')->setValue(0);
        $form->get('stock')->get('criticalthreshold')->setValue(0);

        $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['cancel']) == 1) {
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'inventory', 'action' => 'list']);
                return $this->getResponse();
            }
            if (isset($data['update']) == 1) {
                $form->setData($data);
                if ($form->isValid()) {
                    $stock->setDescription($form->get('stock')->get('description')->getValue());
                    if ($form->get('stock')->get('stockmergement_id')->getValue() != ''){
                        $merge = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($form->get('stock')->get('stockmergement_id')->getValue());
                        $stock->setStockMergement($merge[0]);
                    }
                    if ($form->get('stock')->get('chkautobarcode')->getValue() == 'auto') {
                        $bc = $this->generatebarcodeAction();
                        $stock->setBarcode($bc->getVariables('newbarcode')['newbarcode']);
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
                    $this->doctrine->persist($stock);
                    $this->doctrine->flush();

                    if ($form->get('stock')->get('stockmergement_id')->getValue() != '') {
                        //read the quantity in stock table
                        $stockQty = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findCountQuantityForMergeId($form->get('stock')->get('stockmergement_id')->getValue());
                        $quantity = $stockQty[0]['quantity'];
                        //update stock quantity in StockMergement table
                        $merge[0]->setNetquantity(intval($quantity));
                        $this->doctrine->persist($merge[0]);
                        $this->doctrine->flush();
                    }

                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'inventory', 'action' => 'list']);
                    return $this->getResponse();
                }
                else {
                }
            }
        }

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

    public function editAction() {
        $id = $this->params()->fromRoute('id', 1);

        $request = $this->getRequest();

        $stocks = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findByStockId($id);
        $stock = $stocks[0];

        $form = new StockForm($this->doctrine);

        $form->get('stock')->get('stockmergement_id')->setValueOptions($this->LoadShortDescriptionValues());
        $form->get('stock')->get('status')->setAttributes([
                'value' => $stock->getStatus()
            ]
        );
        if ($stock->getStockMergement() != null) {
            $form->get('stock')->get('stockmergement_id')->setAttributes([
                    'value' => $stock->getStockMergement()->getId()
                ]
            );
        }

        $form->setBindOnValidate(false);
        $form->bind($stock);

        $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['backToList']) == 1) {
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'inventory', 'action' => 'list']);
                return $this->getResponse();
            }
            if (isset($data['cancel']) == 1) {
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'inventory', 'action' => 'display', 'id' => $id]);
                return $this->getResponse();
            }
            if (isset($data['update']) == 1) {
                $form->setData($data);
                if ($form->isValid()) {
                    $stock->setDescription($form->get('stock')->get('description')->getValue());
                    if ($form->get('stock')->get('stockmergement_id')->getValue() != '') {
                        $merge = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($form->get('stock')->get('stockmergement_id')->getValue());
                        $stock->setStockMergement($merge[0]);
                    }
                    if ($form->get('stock')->get('chkautobarcode')->getValue() == 'auto') {
                        $bc = $this->generatebarcodeAction();
                        $stock->setBarcode($bc->getVariables('newbarcode')['newbarcode']);
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
                    $this->doctrine->persist($stock);
                    $this->doctrine->flush();

                    if ($form->get('stock')->get('stockmergement_id')->getValue() != '') {
                        //only one prefered article per stockmergement
                        if ($form->get('stock')->get('prefered')->getValue() == '1'){
                            $merged = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findByAllMergeIdExceptCurrent($form->get('stock')->get('stockmergement_id')->getValue(),$form->get('stock')->get('id')->getValue());
                            foreach ($merged as $m){
                                $m->setPrefered(99);
                                $this->doctrine->persist($m);
                                $this->doctrine->flush();
                            }
                        }

                        //read the quantity in stock table
                        $stockQty = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findCountQuantityForMergeId($form->get('stock')->get('stockmergement_id')->getValue());
                        $quantity = $stockQty[0]['quantity'];
                        //update stock quantity in StockMergement table
                        $merge[0]->setNetquantity(intval($quantity));
                        $this->doctrine->persist($merge[0]);
                        $this->doctrine->flush();
                    }

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
                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'inventory', 'action' => 'display', 'id' => $id]);
                    return $this->getResponse();
                }
                else {
                }
            }
        }

        return new ViewModel([
            'stock' => $stock,
            'form' => $form,
        ]);
    }

    /**
     * Return the barcode like a barcode image (displayed in the article details)
     */
    public function barcodeAction(){
        $id = $this->params()->fromRoute('id', 0);
        $barcodeOptions = array('text' => substr($id, 0, 12));
        $rendererOptions = array();
        return Barcode::factory('ean13', 'image', $barcodeOptions, $rendererOptions)->render();
    }

    /**
     * From the article list, add 1 quantity of the article to the shopping list table
     */
    public function shoppinglistAction(){
        $id = $this->params()->fromRoute('id', 0);

        $stockEntity = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findByStockId($id);
        $stock = $stockEntity[0];

        $stockMergementEntity = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($stock->getStockMergement()->getId());
        $stockMergement = $stockMergementEntity[0];

        $shopStock = $this->doctrine->getRepository('Warehouse\Entity\ShoppingList')->findByStockMergementId($stockMergement->getId());
        if (isset($shopStock) and $shopStock!= null and count($shopStock[0]) <> 0) {
            $shoppingList = $shopStock[0];
            $quantity = $shopStock[0]->getQuantity() + 1;
        } else {
            $shoppingList = new ShoppingList();
            $quantity = 1;
            $shoppingList->setDescription($stockMergement->getDescription());
            $section = $this->doctrine->getRepository('Warehouse\Entity\Section')->findBySettingId($stockMergement->getSection()->getId(),'section');
            $shoppingList->setSection($section[0]);
            $supplier = $this->doctrine->getRepository('Warehouse\Entity\Supplier')->findBySettingId($stockMergement->getSupplier()->getId(),'supplier');
            $shoppingList->setSupplier($supplier[0]);
            $shoppingList->setPriority(1);
//            $shoppingList->setPriority(EnumPriority::PRIORITY_MAJOR);
            $unit = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId(1,'measureunit');
            $shoppingList->setStockmergement($stockMergement);
            $shoppingList->setMeasureUnit($unit[0]);
            $area = $this->doctrine->getRepository('Warehouse\Entity\Area')->findBySettingId($stockMergement->getArea()->getId(),'area');
            $shoppingList->setArea($area[0]);
            $shoppingList->setRecipe(null);
        }
        $shoppingList->setSendtostock(1);
        $shoppingList->setQuantity($quantity);
        $shoppingList->setStatus(\Warehouse\Controller\ShoppingController::SHOPPING_LIST_STATUS_NEW_TO_BUY);

        $this->doctrine->persist($shoppingList);
        $this->doctrine->flush();

        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stock', 'action' => 'list']);
        return $this->getResponse();
    }

    /**
     * Return the next available "free" barcode value
     * @return string
     */
    public function generatebarcodeAction(){
        //read the last barcode value generated
        $lastBC = $this->doctrine->getRepository('Warehouse\Entity\Appsettings')->findByReference($this->config['app_settings_entry']['last_barcode_auto_generated']);
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
        $this->doctrine->persist($lastBC[0]);
        $this->doctrine->flush();

        return new JsonModel([
            'newbarcode' => $newBarCode,
        ]);
    }

    /**
     * Return an array of the existing short description
     * @return array
     */
    protected function LoadShortDescriptionValues() {
        //load Area list
        $area = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findAllOrderByDescription();
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
        $fileName = $this->CreateXLSInventoryList();

        $file = '<a href="#" onclick="OpenRLink(\'' . $this->config['url']['inventory_xls'] . '/' . $fileName . '\');">' . $fileName . '</a>';
        $response = $this->getResponse();
        $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
        $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" => '', "file" => $file)));
        return $response;
    }

    protected function CreateXLSInventoryList(): string
    {
        $now      = date('Y-m-d');
        $path     = $this->config['path']['inventory_xls'] . '/';
        $fileName = 'Inventory_' . $now . '.xls';

        $fp = fopen($path . '/' . $fileName, 'w');

        $inventory = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findAllOrderByDescription();
        $list = [];
        array_push($list,[
            'Id',
            'Barcode',
            'Description',
            'Quantity',
            'status',
        ]);
        foreach ($inventory as $inv) {
            $status = '';
            switch ($inv->getStatus() == $this::ENABLED) {
                case $this::ENABLED:
                    $status = 'Enabled';
                    break;
                case $this::DISABLED:
                    $status = 'Disabled';
                    break;
                case $this::BLOCKED:
                    $status = 'Blocked';
                    break;
            }
            array_push($list,[
                $inv->getId(),
                $inv->getBarcode(),
                $inv->getDescription(),
                $inv->getQuantity(),
                $status,
            ]);

        };

        foreach ($list as $l) {
            fputcsv($fp, $l, "\t", '"');
        }

        fclose($fp);

        return $fileName;
    }
}
