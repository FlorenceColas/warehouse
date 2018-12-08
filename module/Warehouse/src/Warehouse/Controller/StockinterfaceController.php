<?php
namespace Warehouse\Controller;

use Warehouse\Enum\EnumStockMovementType;
use Warehouse\Enum\EnumTableSettings;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class StockinterfaceController extends AbstractActionController
{
    protected $doctrine;

    public function __construct($doctrine) {
        $this->doctrine = $doctrine;
    }

    public function listAction()
    {
        $stockinterface = $this->doctrine->getRepository('Warehouse\Entity\StockInterface')->findAllOrderBySensDescription();
        $units = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findAllOrderByDescription(EnumTableSettings::MEASUREUNIT);
        $stock = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findAllMergedOrderByMergePreferedDescription();

        $stockNetQuantity = array();
        $stockQuantity = array();
        foreach($stock as $s){
            $stockNetQuantity[$s->getId()] = $s->getNetquantity();
            $stockQuantity[$s->getId()] = $s->getQuantity();
        }

        return new ViewModel([
            'stockinterface' => $stockinterface,
            'units' => $units,
            'stock' => $stock,
            'stockNetQty' => $stockNetQuantity,
            'stockQty' => $stockQuantity,
        ]);
    }

    public function updateAction(){
        $stockId = $this->params()->fromQuery('stock','');
        $qty = $this->params()->fromQuery('quantity','');
        $unitId = $this->params()->fromQuery('unit','');
        $qtyToInt = $this->params()->fromQuery('quantitytointegrate','');
        $unitIdToInt = $this->params()->fromQuery('unittointegrate','');

        $stockInterfaceId = $this->params()->fromRoute('id', 0);
        $stockInterface = $this->doctrine->getRepository('Warehouse\Entity\StockInterface')->findByStockInterfaceId($stockInterfaceId);
        $stockInt = $stockInterface[0];

        if ($stockId != '') {
            $stock = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findByStockId($stockId);
            if ($stock[0]->getQuantity() <= 0){
                $qty = '<font color="red">'.$stock[0]->getQuantity().'</font>' . ' x ' .$stock[0]->getNetquantity();
            } else {
                $qty = '<font color="green">'.$stock[0]->getQuantity().'</font>' . ' x ' .$stock[0]->getNetquantity();
            }
            $stockInt->setStock($stock[0]);
        } elseif ($qty != '') {
            $stockInt->setQuantity($qty);
        } elseif ($unitId != '') {
            $unit = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($unitId, EnumTableSettings::MEASUREUNIT);
            $stockInt->setUnit($unit[0]);
        } elseif ($qtyToInt != '') {
            $stockInt->setQuantitytointegrate($qtyToInt);
        } elseif ($unitIdToInt != '') {
            $unit = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($unitIdToInt, EnumTableSettings::MEASUREUNIT);
            $stockInt->setUnittointegrate($unit[0]);
        }
        $this->doctrine->persist($stockInt);
        $this->doctrine->flush();

        $response = $this->getResponse();
        $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
        $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "netqty" => $qty)));
        return $response;
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        if ($id != 0) {
            $stockInterface = $this->doctrine->getRepository('Warehouse\Entity\StockInterface')->findByStockInterfaceId($id);
            $si = $stockInterface[0];
            $this->doctrine->remove($si);
            $this->doctrine->flush();
        }
        $response = $this->getResponse();
        $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
        $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" => '')));
        return $response;
    }

    public function integrateAction() {
        $stockInterface = $this->doctrine->getRepository('Warehouse\Entity\StockInterface')->findAllOrderBySensDescription();
        foreach ($stockInterface as $sI) {
            $stock = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findByStockId($sI->getStock()->getId());
            if ($sI->getSens() == \Warehouse\Controller\ShoppingController::MOVEMENT_SHOP_ADD){
                $stock[0]->setQuantity($stock[0]->getQuantity() + $sI->getQuantitytointegrate());
            } else {
                $stock[0]->setQuantity($stock[0]->getQuantity() - $sI->getQuantitytointegrate());
            }
            $this->doctrine->persist($stock[0]);
            $this->doctrine->flush();

            //read the quantity in stock table
            $stockQty = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findCountQuantityForMergeId($sI->getStockMergement()->getId());
            $quantityMerge = $stockQty[0]['quantity'];
            //update stock quantity in StockMergement table
            $stockMerge = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($sI->getStockMergement()->getId());
            $stockMerge[0]->setNetquantity(intval($quantityMerge));
            $this->doctrine->persist($stockMerge[0]);
            $this->doctrine->flush();
        }

        $q = $this->doctrine->createQuery('delete from Warehouse\Entity\StockInterface');
        $q->execute();

        $this->redirect()->toRoute('warehouse/default', ['controller' => 'stockinterface', 'action' => 'list']);
        return $this->getResponse();
    }
}
