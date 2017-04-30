<?php
/**
 * User: FlorenceColas
 * Date: 28/02/16
 * Version: 1.00
 * SettingsController: manage settings tables which are listed in EnumTableSettings class
 * It contains the following actions:
 *      - list: return the setting table content
 *      - edit: edit the element (json)
 *      - update: persist modification
 *      - add: add a new element (json)
 *      - delete: delete the element
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *  29/04/2017 - integrate session/authentication
 */
namespace Warehouse\Controller;

use Doctrine\ORM\EntityManager;
use Warehouse\Entity\Appsettings;
use Warehouse\Entity\Area;
use Warehouse\Entity\MeasureUnit;
use Warehouse\Entity\RecipeCategory;
use Warehouse\Entity\Section;
use Warehouse\Entity\Supplier;
use Warehouse\Enum\EnumAvailability;
use Warehouse\Enum\EnumComparaison;
use Warehouse\Enum\EnumTableSettings;
use Warehouse\Form\SettingsForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SettingsController extends AbstractActionController
{
    protected $entityManager;
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
     * List of the setting table value
     * @return ViewModel
     */
    public function listAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $em = $this->getEntityManager();

                $page = $this->params()->fromRoute('page', 1);
                $table = $this->params()->fromQuery('table');

                if ($table === '') $table = EnumTableSettings::AREA;
                $form = new SettingsForm($em, $table);
                $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());

                $viewModel = new ViewModel();

                $limit = 8;
                $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
                switch ($table) {
                    case EnumTableSettings::MEASUREUNIT:
                        $settings = $em->getRepository('Warehouse\Entity\\'.EnumTableSettings::MEASUREUNIT)->getPagedSettings($offset, $limit, EnumTableSettings::MEASUREUNIT);
                        break;
                    case EnumTableSettings::SECTION:
                        $settings = $em->getRepository('Warehouse\Entity\\'.EnumTableSettings::SECTION)->getPagedSettings($offset, $limit, EnumTableSettings::SECTION);
                        break;
                    case EnumTableSettings::AREA:
                        $settings = $em->getRepository('Warehouse\Entity\\'.EnumTableSettings::AREA)->getPagedSettings($offset, $limit, EnumTableSettings::AREA);
                        break;
                    case EnumTableSettings::SUPPLIER:
                        $settings = $em->getRepository('Warehouse\Entity\\'.EnumTableSettings::SUPPLIER)->getPagedSettings($offset, $limit, EnumTableSettings::SUPPLIER);
                        break;
                    case EnumTableSettings::RECIPE_CATEGORY:
                        $settings = $em->getRepository('Warehouse\Entity\\'.EnumTableSettings::RECIPE_CATEGORY)->getPagedSettings($offset, $limit, EnumTableSettings::RECIPE_CATEGORY);
                        break;
                    case EnumTableSettings::APPSETTINGS:
                        $settings = $em->getRepository('Warehouse\Entity\\'.EnumTableSettings::APPSETTINGS)->getPagedSettings($offset, $limit, EnumTableSettings::APPSETTINGS);
                        break;
                    default:
                        $table = EnumTableSettings::AREA;
                        $settings = $em->getRepository('Warehouse\Entity\\'.EnumTableSettings::AREA)->getPagedSettings($offset, $limit, EnumTableSettings::AREA);
                }

                $viewModel->setVariables([
                    'pagedSettings' => $settings,
                    'page' => $page,
                    'table' => $table,
                    'settingsForm' => $form,
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
     * Return json code for edit action of the element in parameter, in the setting table in parameter
     */
    public function editAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $table = $this->params()->fromQuery('table','');
                $id = $this->params()->fromRoute('id', 0);
                $em = $this->getEntityManager();
                $request = $this->getRequest();

                $viewModel->setTerminal($request->isXmlHttpRequest());

                //read the setting details
                $settings = $em->getRepository('Warehouse\Entity\\'.$table)->findBySettingId($id,$table);
                $setting = $settings[0];

                $html = '<h3>Update</h3>';
                $html = $html . '<form method="post" name="settings-form" action="?table='.$table.'" id="settings-form">';
                $html = $html . '<input type="hidden" id="id" name="id" value="' . $setting->getId() . '">';
                if ($table == EnumTableSettings::APPSETTINGS){
                    $html = $html . '<p>Reference: <textarea id= "reference" name="reference" maxlength="255" cols="50" rows="1">'. $setting->getSettingReference() .'</textarea>';
                    $html = $html . '<p>Value: <input type="text" id= "value" name="value" value="' . $setting->getSettingValue() . '">';

                } else {
                    $html = $html . '<p>Description: <input type="text" id= "description" name="description" value="' . $setting->getDescription() . '">';
                    if ($table == EnumTableSettings::MEASUREUNIT) {
                        $html = $html . ' Abbreviation: <input type="text" id="abbreviation" name="abbreviation" value="' . $setting->getUnit() . '">';
                        if ($setting->getUseinstock() == 1)
                            $html = $html . ' Available in Stock: <input type="checkbox" id="useinstock" name="useinstock" value="" checked>';
                        else
                            $html = $html . ' Available in Stock: <input type="checkbox" id="useinstock" name="useinstock" value="">';
                    } else {
                        $html = $html . '  <input type="hidden" id="abbreviation" name="abbreviation" value="">';
                        $html = $html . '  <input type="hidden" id="comparaison" name="comparaison" value="">';
                        $html = $html . '  <input type="hidden" id="useinstock" name="useinstock" value="">';
                    }
                    if ($table == EnumTableSettings::SECTION) {
                        //load Area list
                        $area = $this->getEntityManager()->getRepository('Warehouse\Entity\Area')->findAllOrderByDescription(EnumTableSettings::AREA);
                        $options = array();
                        foreach($area as $st) {
                            $options[$st->getId()] = $st->getDescription();
                        }
                        $html = $html . ' Area: <select name="area_id" id="area_id">';
                        foreach($options as $key => $value) {
                            if ($key == $setting->getArea()->getId())
                                $html = $html . '<option value="'.$key.'" selected>'.$value.'</option>';
                            else
                                $html = $html . '<option value="'.$key.'">'.$value.'</option>';
                        }
                        $html = $html . '</select>';
        //                $html = $html . '  <input type="hidden" id="area_id" name="area_id" value="' . $setting->getArea()->getId() . '">';
                    } else {
                        $html = $html . '  <input type="hidden" id="area_id" name="area_id" value="0">';
                    }
                }
                $html = $html . '</p><p><input name="update" type="submit" onclick="updateSetting()" id="update" value="Update"></p>';
                $html = $html . '</form>';

                $response = $this->getResponse();
                $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'contentpage' => $html)));
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
     * Persist setting modification
     */
    public function updateAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $table = $this->params()->fromQuery('table','');
                $id = $this->params()->fromRoute('id', 0);
                $em = $this->getEntityManager();
                $request = $this->getRequest();

                $form = new SettingsForm($em, $table);

                $viewModel->setTerminal($request->isXmlHttpRequest());

                //read the setting details
                if ($id != 0) {
                    $settings = $em->getRepository('Warehouse\Entity\\' . $table)->findBySettingId($id, $table);
                    $setting = $settings[0];
                } else {
                    switch ($table){
                        case EnumTableSettings::MEASUREUNIT:
                            $setting = new MeasureUnit();
                            break;
                        case EnumTableSettings::SECTION:
                            $setting = new Section();
                            break;
                        case EnumTableSettings::AREA:
                            $setting = new Area();
                            break;
                        case EnumTableSettings::SUPPLIER:
                            $setting = new Supplier();
                            break;
                        case EnumTableSettings::RECIPE_CATEGORY:
                            $setting = new RecipeCategory();
                            break;
                        case EnumTableSettings::APPSETTINGS:
                            $setting = new Appsettings();
                            break;
                    }
                }

                $data = $this->params()->fromPost();
                $form->setData($data);
                if ($form->isValid()) {
                    if ($table == EnumTableSettings::APPSETTINGS){
                        $setting->setSettingReference($request->getPost()->get("reference"));
                        $setting->setSettingValue($request->getPost()->get("value"));
                    } else {
                        $description = $request->getPost()->get("description");
                        $setting->setDescription($description);

                        if ($table === EnumTableSettings::MEASUREUNIT) {
                            $setting->setUnit($request->getPost()->get("abbreviation"));
                            $setting->setComparaison(0);
                            if (isset($data["useinstock"]))
                                $setting->setUseinstock(1);
                            else
                                $setting->setUseinstock(0);
                        }

                        if ($table === EnumTableSettings::SECTION) {
                            $areas = $em->getRepository('Warehouse\Entity\Area')->findBySettingId($request->getPost()->get("area_id"), 'area');
                            $setting->setArea($areas[0]);
                        }
                    }
                    $em->persist($setting);
                    $em->flush();

                    $html = 'update successful';
                    $response = $this->getResponse();
                    $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'contentpage' => $html)));
                    return $response;
                } else {
                    $html = 'form not valid';
                    $response = $this->getResponse();
                    $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'contentpage' => $html)));
                    return $response;
                }
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
     * Return json code to add a new setting value
     */
    public function addAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $table = $this->params()->fromQuery('table','');
                switch ($table){
                    case EnumTableSettings::MEASUREUNIT:
                        $html = '<h3>New Measure Unit</h3>';
                        break;
                    case EnumTableSettings::SECTION:
                        $html = '<h3>New Section</h3>';
                        //load Area list
                        $area = $this->getEntityManager()->getRepository('Warehouse\Entity\Area')->findAllOrderByDescription(EnumTableSettings::AREA);
                        $options = array();
                        foreach($area as $st) {
                            $options[$st->getId()] = $st->getDescription();
                        }
                        break;
                    case EnumTableSettings::AREA:
                        $html = '<h3>New Area</h3>';
                        break;
                    case EnumTableSettings::SUPPLIER:
                        $html = '<h3>New Supplier</h3>';
                        break;
                    case EnumTableSettings::RECIPE_CATEGORY:
                        $html = '<h3>New Recipe Category</h3>';
                        break;
                    case EnumTableSettings::APPSETTINGS:
                        $html = '<h3>New Application Setting</h3>';
                        break;
                }

                $request = $this->getRequest();
                $viewModel->setTerminal($request->isXmlHttpRequest());

                $html = $html . '<form method="post" name="settings-form" action="?table='.$table.'" id="settings-form">';
                $html = $html . '<input type="hidden" id="id" name="id" value="0">';
                if ($table == EnumTableSettings::APPSETTINGS){
                    $html = $html . '<p>Reference: <textarea id= "reference" name="reference" maxlength="255" cols="50" rows="1"></textarea>';
                    $html = $html . '<p>Value: <input type="text" id= "value" name="value" value="">';

                } else {
                    $html = $html . '<p>Description: <input type="text" id= "description" name="description" value="">';
                    if ($table === EnumTableSettings::MEASUREUNIT) {
                        $html = $html . ' Abbreviation: <input type="text" id="abbreviation" name="abbreviation" value="">';
                        $html = $html . ' Available in Stock: <input type="checkbox" id="useinstock" name="useinstock" value="">';
                        $html = $html . '  <input type="hidden" id="comparaison" name="comparaison" value="">';

                    } else {
                        $html = $html . '  <input type="hidden" id="abbreviation" name="abbreviation" value="">';
                        $html = $html . '  <input type="hidden" id="comparaison" name="comparaison" value="">';
                        $html = $html . '  <input type="hidden" id="useinstock" name="useinstock" value="">';
                    }
                    if ($table === EnumTableSettings::SECTION) {
                        $html = $html . ' Area: <select name="area_id" id="area_id">';
                        foreach ($options as $key => $value) {
                            $html = $html . '<option value="' . $key . '">' . $value . '</option>';
                        }
                        $html = $html . '</select>';
                    } else {
                        $html = $html . ' <input type="hidden" id="area" name="area" value="">';
                    }
                }
                $html = $html . '</p><p><input name="update" type="submit" onclick="updateSetting()" id="update" value="Update"></p>';
                $html = $html . '</form>';

                $response = $this->getResponse();
                $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'contentpage' => $html)));
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
     * Delete the element in parameter
     */
    public function deleteAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $id = $this->params()->fromRoute('id', 0);
                $table = $this->params()->fromQuery('table','');

                //read the record in DB
                $setEntity = $this->getEntityManager()->getRepository('Warehouse\Entity\\'.$table)->findBySettingId($id,$table);
                $setting = $setEntity[0];
                //remove record in DB
                $this->getEntityManager()->remove($setting);
                $this->getEntityManager()->flush();

                $response = $this->getResponse();
                $response->getHeaders()->addHeaders(array('Content-Type'=>'application/json;charset=UTF-8'));
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
}