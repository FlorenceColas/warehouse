<?php
/**
 * User: FlorenceColas
 * Date: 05/12/16
 * Version: 1.00
 * ToolsController: contains tools functions action.
 *      - generatebarcode: generate the next available barcode (free zone)
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *  29/04/2017 - integrate session/authentication
 */

namespace Warehouse\Controller;

use Doctrine\ORM\EntityManager;
use Warehouse\Enum\EnumAppSettingsReferences;
use Zend\Mvc\Controller\AbstractActionController;

class ToolsController extends AbstractActionController
{
    protected $entityManager;
    protected $authservice;
    protected $audittrailservice;

    public function generatebarcodeAction()
    {
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
        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'barcode' => $newBarCode)));
        return $response;
    }

}