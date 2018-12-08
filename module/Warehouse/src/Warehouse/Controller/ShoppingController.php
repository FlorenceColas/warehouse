<?php

namespace Warehouse\Controller;

use Warehouse\Entity\ShoppingList;
use Warehouse\Entity\StockInterface;
use Warehouse\Enum\EnumTableSettings;
use Warehouse\Enum\EnumUnit;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ShoppingController extends AbstractActionController
{
    const MOVEMENT_SHOP_ADD               = 1;
    const MOVEMENT_SHOP_REMOVE            = -1;
    const SHOPPING_LIST_STATUS_BOUGHT     = 2;
    const SHOPPING_LIST_STATUS_NEW_TO_BUY = 1;
    const SHOPPING_LIST_STATUS_TO_BUY     = 0;

    protected $config;
    protected $doctrine;

    public function __construct($doctrine, $config) {
        $this->doctrine = $doctrine;
        $this->config   = $config;
    }

    public function listAction()
    {
        $shoppingList = $this->doctrine->getRepository('Warehouse\Entity\ShoppingList')->findAllOrderBySectionDescription();
        $units = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findAllOrderByDescription(EnumTableSettings::MEASUREUNIT);
        $stock = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findAllOrderByDescription();
        $arrStock = array();
        foreach ($stock as $s) {
            $arrStock[$s->getId()] = $s->getDescription();
        }

        return new ViewModel([
            'arrstock'     =>$arrStock,
            'config'       => $this->config,
            'shoppinglist' => $shoppingList,
            'units'        => $units,
        ]);
    }

    public function updateAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $unitId = $this->params()->fromQuery('unit','');
        $quantity = $this->params()->fromQuery('quantity','');
        $priority = $this->params()->fromQuery('priority','');
        $sendtostock = $this->params()->fromQuery('sendtostock','');

        $shoppinglist = $this->doctrine->getRepository('Warehouse\Entity\ShoppingList')->findByShoppingListId($id);
        $sl = $shoppinglist[0];

        $update = false;
        if ($unitId !== ""){
            $set = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($unitId,EnumTableSettings::MEASUREUNIT);
            $unit = $set[0];
            $sl->setMeasureUnit($unit);
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
            $sl->setStatus($this::SHOPPING_LIST_STATUS_NEW_TO_BUY);
            $this->doctrine->persist($sl);
            $this->doctrine->flush();
        }

        $response = $this->getResponse();
        $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
        $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" => '')));
        return $response;
    }

    public function addAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $shoppinglist = $this->doctrine->getRepository('Warehouse\Entity\ShoppingList')->findByStockMergementId($id);
        if (isset($shoppinglist) and $shoppinglist!= null and count($shoppinglist[0]) <> 0) {
            $sl = $shoppinglist[0];
            $sl->setQuantity($sl->getQuantity() + 1);
        } else {
            $stock = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($id);
            $sl = new ShoppingList();
            $sl->setStockmergement($stock[0]);
            $sl->setDescription($stock[0]->getDescription());
            $sl->setQuantity(1);
            $defaultUnitId = $this->doctrine->getRepository('Warehouse\Entity\Appsettings')->findByReference($this->config['app_settings_entry']['default_shopping_list_unit']);
            $unit = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($defaultUnitId[0]->getSettingvalue(), EnumTableSettings::MEASUREUNIT);
            $sl->setMeasureUnit($unit[0]);
            $sl->setSection($stock[0]->getSection());
            $sl->setSupplier($stock[0]->getSupplier());
            $sl->setPriority(1);
            $sl->setArea($stock[0]->getArea());
            $sl->setSendtostock(1);
        }
        $sl->setStatus($this::SHOPPING_LIST_STATUS_NEW_TO_BUY);
        $this->doctrine->persist($sl);
        $this->doctrine->flush();

        $response = $this->getResponse();
        $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
        $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" => '')));
        return $response;
    }

    public function validateAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        $shoppinglist = $this->doctrine->getRepository('Warehouse\Entity\ShoppingList')->findByShoppingListId($id);
        $sl = $shoppinglist[0];

        if ($sl->getStatus() == $this::SHOPPING_LIST_STATUS_NEW_TO_BUY) $status = $this::SHOPPING_LIST_STATUS_TO_BUY;
        elseif ($sl->getStatus() == $this::SHOPPING_LIST_STATUS_TO_BUY) $status = $this::SHOPPING_LIST_STATUS_BOUGHT;
        else $status = $this::SHOPPING_LIST_STATUS_TO_BUY;

        $sl->setStatus($status);

        $this->doctrine->persist($sl);
        $this->doctrine->flush();

        $response = $this->getResponse();
        $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
        $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" => '')));
        return $response;
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        if ($id != 0) {
            $shoppinglist = $this->doctrine->getRepository('Warehouse\Entity\ShoppingList')->findByShoppingListId($id);
            $sl = $shoppinglist[0];
            $this->doctrine->remove($sl);
            $this->doctrine->flush();
        } else {
            //delete all records
            $q = $this->doctrine->createQuery('delete from Warehouse\Entity\ShoppingList');
            $q->execute();
        }
        $response = $this->getResponse();
        $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
        $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" => '')));
        return $response;
    }

    public function exportxlsAction()
    {
        $fileName = $this->CreateXLSShoppingList();

        $file = '<a href="#" onclick="OpenRLink(\'' . $this->config['url']['shopping_list_xls'] . '/' . $fileName . '\');">' . $fileName . '</a>';
        $response = $this->getResponse();
        $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
        $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" => '', "file" => $file)));
        return $response;
    }

    protected function CreateXLSShoppingList(): string
    {
        $now      = date('Y-m-d');
        $path     = $this->config['path']['shopping_list_xls'] . '/';
        $fileName = 'ShoppingList_' . $now . '.xls';

        $fp = fopen($path . '/' . $fileName, 'w');

        $shoppinglist = $this->doctrine->getRepository('Warehouse\Entity\ShoppingList')->findByAll();
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
                    $sl->getQuantity().' '.$sl->getMeasureUnit()->getUnit(),
                    $sl->getSection()->getDescription(),
                    $sl->getSupplier()->getDescription(),
                    $sl->getArea()->getDescription(),
                ]);
            else
                array_push($list,[
                    '',
                    $sl->getDescription(),
                    $sl->getQuantity().' '.$sl->getMeasureUnit()->getUnit(),
                    $sl->getSection()->getDescription(),
                    $sl->getSupplier()->getDescription(),
                    $sl->getArea()->getDescription(),
                ]);
        };

        foreach ($list as $l) {
            fputcsv($fp, $l, "\t", '"');
        }

        fclose($fp);

        return $fileName;
    }

    protected function CreateHTMLShoppingList(): string
    {
        $shoppinglist = $this->doctrine->getRepository('Warehouse\Entity\ShoppingList')->findByAll();

        $now      = date('Y-m-d');
        $path     = $this->config['path']['shopping_list_html'] . '/';
        $fileName = 'ShoppingList_' . $now . '.html';
        $fp = fopen($path . '/' . $fileName, 'w');

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
            $html = $html . '<td>' . $sl->getMeasureUnit()->getDescription() . '</td>';
            $html = $html . '<td>' . $sl->getArea()->getDescription() . '</td>';
            $html = $html . '</tr> ';
            fwrite($fp, $html);
        }
        $html = '</tbody></table></div>';
        fwrite($fp, $html);
        fclose($fp);

        return $fileName;
    }

    public function sendmailAction()
    {
        $pathHtml     = $this->config['path']['shopping_list_html'] . '/';
        $pathXls      = $this->config['path']['shopping_list_xls'] . '/';
        $fileNameXls  = $this->CreateXLSShoppingList();
        $fileNamehtml = $this->CreateHTMLShoppingList();

        $arrayHtml = array();

        $attachment = new \Zend\Mime\Part(fopen($pathHtml . $fileNamehtml, 'r'));
        $attachment->type     = 'text/html';
        $attachment->charset  = 'utf-8';
        $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
        $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_INLINE;
        $attachment->filename = $fileNamehtml;
        array_push($arrayHtml,$attachment);

        $attachment = new \Zend\Mime\Part(fopen($pathXls . $fileNameXls, 'r'));
        $attachment->type     = 'application/vnd.ms-excel';
        $attachment->charset  = 'utf-8';
        $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
        $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
        $attachment->filename = $fileNameXls;
        array_push($arrayHtml,$attachment);

        $options   = new SmtpOptions($this->config['smtpOptions']);
        $transport = new Smtp($options);

        $message = new Message();

        $message->setFrom($this->config['from']);

        if ('' === 'colasflorence@laposte.net') {
            $message->addTo($this->config['to']);
        } else {
            $message->addTo('colasflorence@laposte.net');
        }
        $message->setSubject('Shopping List - ' . date('d/m/Y'));

        $message->setEncoding("UTF-8");
        $message->setBody('message');

        $transport->send($message);

        $this->redirect()->toRoute('warehouse/default', ['controller' => 'shopping', 'action' => 'list']);

        return $this->getResponse();
    }

    public function sendtostockinterfaceAction()
    {
        $shoplist = $this->doctrine->getRepository('Warehouse\Entity\ShoppingList')->findByAllSendToStock();
        foreach ($shoplist as $sp) {
            $stockInterface = new StockInterface();
            $stockInterface->setDescription($sp->getDescription());
            $stockInterface->setStockMergement($sp->getStockmergement());
            $stockInterface->setSens($this::MOVEMENT_SHOP_ADD);
            $stockInterface->setQuantity($sp->getQuantity());
            $stockInterface->setMeasureUnit($sp->getMeasureUnit());

            $stockPrefered = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findByPreferedMergeId($sp->getStockmergement()->getId());
            $stockInterface->setStock($stockPrefered[0]);

            $qtyU = 0;
            switch ($sp->getMeasureUnit()->getId()) {
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

            $unit = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId(EnumUnit::UNIT_PIECE, EnumTableSettings::MEASUREUNIT);
            $stockInterface->setUnittointegrate($unit[0]);

            if ($sp->getMeasureUnit()->getId() == EnumUnit::UNIT_PIECE) {
                $stockInterface->setQuantitytointegrate($sp->getQuantity());
            } else {
                if ($sp->getStockmergement()->getEqpiece() != 0) {
                    $qty = $qtyU / $sp->getStockmergement()->getEqpiece();
                    if ($qty < 1) $qty = 1;
                } else $qty = $sp->getQuantity();
                $stockInterface->setQuantitytointegrate($qty);
            }

            $this->doctrine->persist($stockInterface);
            $this->doctrine->flush();
        }

        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockinterface', 'action' => 'list']);
        return $this->getResponse();
    }
}
