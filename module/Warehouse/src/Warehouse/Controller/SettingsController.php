<?php
namespace Warehouse\Controller;

use Warehouse\Entity\Appsettings;
use Warehouse\Entity\Area;
use Warehouse\Entity\MeasureUnit;
use Warehouse\Entity\Category;
use Warehouse\Entity\Section;
use Warehouse\Entity\Supplier;
use Warehouse\Enum\EnumTableSettings;
use Warehouse\Form\SettingsForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SettingsController extends AbstractActionController
{
    protected $config;
    protected $doctrine;

    public function __construct(
        array $config,
        $doctrine
    ) {
        $this->config   = $config;
        $this->doctrine = $doctrine;
    }

    /**
     * List of the setting table value
     * @return ViewModel
     */
    public function listAction()
    {
        $table = $this->params()->fromQuery('table');

        if ($table === '') $table = EnumTableSettings::AREA;
        $form = new SettingsForm($this->doctrine, $table);
        $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());

        $viewModel = new ViewModel();

        switch ($table) {
            case EnumTableSettings::MEASUREUNIT:
                $settings = $this->doctrine->getRepository('Warehouse\Entity\\'.EnumTableSettings::MEASUREUNIT)->getFindSettings(EnumTableSettings::MEASUREUNIT);
                break;
            case EnumTableSettings::SECTION:
                $settings = $this->doctrine->getRepository('Warehouse\Entity\\'.EnumTableSettings::SECTION)->getFindSettings(EnumTableSettings::SECTION);
                break;
            case EnumTableSettings::AREA:
                $settings = $this->doctrine->getRepository('Warehouse\Entity\\'.EnumTableSettings::AREA)->getFindSettings(EnumTableSettings::AREA);
                break;
            case EnumTableSettings::SUPPLIER:
                $settings = $this->doctrine->getRepository('Warehouse\Entity\\'.EnumTableSettings::SUPPLIER)->getFindSettings(EnumTableSettings::SUPPLIER);
                break;
            case EnumTableSettings::RECIPE_CATEGORY:
                $settings = $this->doctrine->getRepository('Warehouse\Entity\\'.EnumTableSettings::RECIPE_CATEGORY)->getFindSettings(EnumTableSettings::RECIPE_CATEGORY);
                break;
            case EnumTableSettings::APPSETTINGS:
                $settings = $this->doctrine->getRepository('Warehouse\Entity\\'.EnumTableSettings::APPSETTINGS)->getFindSettings();
                break;
            default:
                $table = EnumTableSettings::AREA;
                $settings = $this->doctrine->getRepository('Warehouse\Entity\\'.EnumTableSettings::AREA)->getFindSettings(EnumTableSettings::AREA);
        }

        $viewModel->setVariables([
            'settings' => $settings,
            'table' => $table,
            'settingsForm' => $form,
        ]);

        return $viewModel;
    }

    /**
     * Return json code for edit action of the element in parameter, in the setting table in parameter
     */
    public function editAction()
    {
        $viewModel = new ViewModel();

        $table = $this->params()->fromQuery('table','');
        $id = $this->params()->fromRoute('id', 0);
        $request = $this->getRequest();

        $viewModel->setTerminal($request->isXmlHttpRequest());

        //read the setting details
        $settings = $this->doctrine->getRepository('Warehouse\Entity\\'.$table)->findBySettingId($id,$table);
        $setting = $settings[0];

        $html = '<h3>Update</h3>';
        $html .= '<form method="post" name="settings-form" href="" id="settings-form">';
        $html .= '<input type="hidden" id="id" name="id" value="' . $setting->getId() . '">';
        if ($table == EnumTableSettings::APPSETTINGS){
            $html .= '<p>Reference: <textarea id= "reference" name="reference" maxlength="255" cols="50" rows="1">'. $setting->getSettingReference() .'</textarea>';
            $html .= '<p>Value: <input type="text" id= "value" name="value" value="' . $setting->getSettingValue() . '">';

        } else {
            $html .= '<p>Description: <input type="text" id= "description" name="description" value="' . $setting->getDescription() . '">';
            if ($table == EnumTableSettings::MEASUREUNIT) {
                $html .= ' Abbreviation: <input type="text" id="abbreviation" name="abbreviation" value="' . $setting->getUnit() . '">';
                if ($setting->getUseinstock() == 1)
                    $html .= ' Available in Stock: <input type="checkbox" id="useinstock" name="useinstock" value="" checked>';
                else
                    $html .= ' Available in Stock: <input type="checkbox" id="useinstock" name="useinstock" value="">';
            } else {
                $html .= '<input type="hidden" id="abbreviation" name="abbreviation" value="">';
                $html .= '<input type="hidden" id="comparaison" name="comparaison" value="">';
                $html .= '<input type="hidden" id="useinstock" name="useinstock" value="">';
            }
            if ($table == EnumTableSettings::SECTION) {
                //load Area list
                $area = $this->doctrine->getRepository('Warehouse\Entity\Area')->findAllOrderByDescription(EnumTableSettings::AREA);
                $options = [];
                foreach($area as $st) {
                    $options[$st->getId()] = $st->getDescription();
                }
                $html = $html . ' Area: <select name="area_id" id="area_id">';
                foreach($options as $key => $value) {
                    if ($key == $setting->getArea()->getId())
                        $html .= '<option value="'.$key.'" selected>'.$value.'</option>';
                    else
                        $html .= '<option value="'.$key.'">'.$value.'</option>';
                }
                $html .= '</select>';
            } else {
                $html .= '<input type="hidden" id="area_id" name="area_id" value="0">';
            }
        }
        $html .= '</p><p><input name="update" type="submit" onclick="updateSetting()" id="update" value="Update"></p>';
        $html .= '</form>';

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode([
            'response' => true,
            'contentpage' => $html,
        ]));
        return $response;
    }

    /**
     * Persist setting modification
     */
    public function updateAction()
    {
        $viewModel = new ViewModel();

        $table = $this->params()->fromQuery('table','');
        $id = $this->params()->fromRoute('id', 0);
        $request = $this->getRequest();

        $form = new SettingsForm($this->doctrine, $table);

        $viewModel->setTerminal($request->isXmlHttpRequest());

        //read the setting details
        if ($id != 0) {
            $settings = $this->doctrine->getRepository('Warehouse\Entity\\' . $table)->findBySettingId($id, $table);
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
                    $setting = new Category();
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
                    $areas = $this->doctrine->getRepository('Warehouse\Entity\Area')->findBySettingId($request->getPost()->get("area_id"), 'area');
                    $setting->setArea($areas[0]);
                }
            }
            $this->doctrine->persist($setting);
            $this->doctrine->flush();

            $this->redirect()->toRoute(
                'warehouse/default',
                ['controller' => 'settings', 'action' => 'list'],
                ['query' => ['table' => $table]]
            );
            return $this->getResponse();
        } else {
            $html = 'form not valid';
            $response = $this->getResponse();
            $response->setContent(\Zend\Json\Json::encode([
                'response' => true,
                'contentpage' => $html,
            ]));
            return $response;
        }
    }

    /**
     * Return json code to add a new setting value
     */
    public function addAction()
    {
        $viewModel = new ViewModel();

        $table = $this->params()->fromQuery('table','');
        $html  = '';

        switch ($table){
            case EnumTableSettings::MEASUREUNIT:
                $html = '<h4>New Measure Unit</h4>';
                break;
            case EnumTableSettings::SECTION:
                $html = '<h4>New Section</h4>';
                //load Area list
                $area = $this->doctrine->getRepository('Warehouse\Entity\Area')->findAllOrderByDescription(EnumTableSettings::AREA);
                $options = [];
                foreach($area as $st) {
                    $options[$st->getId()] = $st->getDescription();
                }
                break;
            case EnumTableSettings::AREA:
                $html = '<h4>New Area</h4>';
                break;
            case EnumTableSettings::SUPPLIER:
                $html = '<h4>New Supplier</h4>';
                break;
            case EnumTableSettings::RECIPE_CATEGORY:
                $html = '<h4>New Recipe Category</h4>';
                break;
            case EnumTableSettings::APPSETTINGS:
                $html = '<h4>New Application Setting</h4>';
                break;
        }

        $request = $this->getRequest();
        $viewModel->setTerminal($request->isXmlHttpRequest());

        $html .= '<form method="post" name="settings-form" href="" id="settings-form">';
        $html .= '<input type="hidden" id="id" name="id" value="0">';
        if ($table == EnumTableSettings::APPSETTINGS){
            $html .= '<p>Reference: <textarea id= "reference" name="reference" maxlength="255" cols="50" rows="1"></textarea>';
            $html .= '<p>Value: <input type="text" id= "value" name="value" value="">';

        } else {
            $html .= '<p>Description: <input type="text" id= "description" name="description" value="">';
            if ($table === EnumTableSettings::MEASUREUNIT) {
                $html .= 'Abbreviation: <input type="text" id="abbreviation" name="abbreviation" value="">';
                $html .= 'Available in Stock: <input type="checkbox" id="useinstock" name="useinstock" value="">';
                $html .= '<input type="hidden" id="comparaison" name="comparaison" value="">';

            } else {
                $html .= '<input type="hidden" id="abbreviation" name="abbreviation" value="">';
                $html .= '<input type="hidden" id="comparaison" name="comparaison" value="">';
                $html .= '<input type="hidden" id="useinstock" name="useinstock" value="">';
            }
            if ($table === EnumTableSettings::SECTION) {
                $html .= 'Area: <select name="area_id" id="area_id">';
                foreach ($options as $key => $value) {
                    $html .= '<option value="' . $key . '">' . $value . '</option>';
                }
                $html .= '</select>';
            } else {
                $html .= '<input type="hidden" id="area" name="area" value="">';
            }
        }
        $html .= '</p><p><input name="update" type="submit" onclick="updateSetting()" id="update" value="Update"></p>';
        $html .= '</form>';

        $response = $this->getResponse();
        $response->getHeaders()->addHeaders(['Content-Type'=>'application/json;charset=UTF-8']);
        $response->setContent(\Zend\Json\Json::encode([
            'result'      => 'success',
            'contentpage' => $html,
        ]));
        return $response;
    }

    /**
     * Delete the element in parameter
     */
    public function deleteAction(){
        $id = $this->params()->fromRoute('id', 0);
        $table = $this->params()->fromQuery('table','');

        //read the record in DB
        $setEntity = $this->doctrine->getRepository('Warehouse\Entity\\'.$table)->findBySettingId($id,$table);
        $setting = $setEntity[0];
        //remove record in DB
        $this->doctrine->remove($setting);
        $this->doctrine->flush();

        $this->redirect()->toRoute(
            'warehouse/default',
            ['controller' => 'settings', 'action' => 'list'],
            ['query' => ['table' => $table]]
        );
        return $this->getResponse();
    }
}
