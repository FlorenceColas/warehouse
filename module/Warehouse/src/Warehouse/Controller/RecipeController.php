<?php
/**
 * User: FlorenceColas
 * Date: 08/01/2017
 * Version: 1.00
 * RecipeController: manage Recipe menu displayed, with search criteria (category part of description).
 * It contains the following actions:
 *      - list: recipe list
 *      - display: display the recipe
 *      - edit: edit the recipe
 *      - add: add a new recipe
 *      - delete: delete the recipe
 *      - shoppinglist: add the article with recipe reference in the shopping list
 *      - generatepdf: generate a pdf with the receipe in parameter
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *  29/04/2017 - integrate session/authentication
 */

namespace Warehouse\Controller;

use Doctrine\ORM\EntityManager;
use Warehouse\Entity\Ingredient;
use Warehouse\Entity\Instruction;
use Warehouse\Entity\Recipe;
use Warehouse\Entity\StockInterface;
use Warehouse\Enum\EnumAvailability;
use Warehouse\Enum\EnumComparaison;
use Warehouse\Enum\EnumPriority;
use Warehouse\Enum\EnumSession;
use Warehouse\Enum\EnumShoppingListStatus;
use Warehouse\Enum\EnumStockMovementType;
use Warehouse\Enum\EnumTableSettings;
use Warehouse\Enum\EnumUnit;
use Warehouse\Form\CriteriaRecipeForm;
use Warehouse\Form\RecipeForm;
use Warehouse\Model\CriteriaRecipe;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Warehouse\Entity\ShoppingList;
use Warehouse\FPDF;

class RecipeController extends AbstractActionController
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
     * List action of the recipes
     */
    public function listAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $page = $this->params()->fromRoute('page', 1);

                //read the session container RECIPESEARCH
                $recipeSession = new Container(EnumSession::RECIPESEARCH);

                //read config file for db connection
                $config = $this->getServiceLocator()->get('Config');
                $adapter = new Adapter($config['db']);
                $sql = new Sql($adapter);

                //query to read all existing section in stock table, and count associated article
                //use in Search criteria
                $select = $sql->select();
                $select->columns(
                    [
                        'category_id as category_id',
                        'description' => new Expression('recipecategory.description'),
                        'counter' => new Expression('COUNT(recipes.id)'),
                    ]);
                $select->from('recipes');
                $select->join('recipecategory', 'recipecategory.id = recipes.category_id', array(), 'left');
                $select->group('category_id');
                $statement = $sql->prepareStatementForSqlObject($select);
                $results = $statement->execute();

                //add category values in an array
                $categoryValues = array();
                $categoryValuesChecked = array();
                foreach ($results as $row) {
                    $categoryValues[$row['category_id']] = $row['description'].' ('.$row['counter'] .')';
                    //if there is no category value, the criteria is checked by default
                    if (!$recipeSession->offsetExists(EnumSession::RECIPESEARCH_CATEGORY)) {
                        $categoryValuesChecked[] = $row['category_id'] . '';
                    }
                }

                //new Search Recipe criteria form implementation
                $formCriteriaRecipeForm = new CriteriaRecipeForm($categoryValues);

                //fill in default values in Criteria Recipe form if session values exist
                if ($recipeSession->offsetExists(EnumSession::RECIPESEARCH_CATEGORY)) {
                    $categoryValuesChecked = $recipeSession->category;
                }

                //populate checked value in section criteria
                $formCriteriaRecipeForm->populateValues([
                    'category' => $categoryValuesChecked,
                ]);

                //fill in action in the form
                $formCriteriaRecipeForm->setAttribute('action' ,$this->getRequest()->getUri()->__toString());

                if ($this->getRequest()->isPost()) {
                    $data = $this->params()->fromPost();

                    $criteriaRecipe = new CriteriaRecipe();
                    $formCriteriaRecipeForm->bind($criteriaRecipe);
                    $formCriteriaRecipeForm->setData($data);

                    //test form valid
                    if (!$formCriteriaRecipeForm->isValid())
                    {
                        var_dump('err:'.sizeof($formCriteriaRecipeForm->getMessages()));
                        foreach($formCriteriaRecipeForm->getMessages('category') as $msgId => $msg) {
                            var_dump('Validation error:'. $msgId. '=>'. $msg);
                        }

                        var_dump('form not valid');
                    }

                    //store criteria in session RecipeSearch
                    $recipeSession->category = $criteriaRecipe->getCategory();

                    $limit = 50;
                    $page = 1;
                    $offset = ($page == 0) ? 0 : ($page - 1) * $limit;

                    $recipe = $this->getEntityManager()->getRepository('Warehouse\Entity\Recipe')->getPagedRecipe($offset, $limit, $criteriaRecipe);
                }
                else {
                    # move to service
                    //    var_dump('page:'.$page);
                    $limit = 50;
                    $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
                    $recipe = $this->getEntityManager()->getRepository('Warehouse\Entity\Recipe')->getPagedRecipe($offset, $limit, null);
                    # end move to service
                }

                $viewModel->setVariables([
                    'pagedRecipe' => $recipe,
                    'page' => $page,
                    'criteriarecipeform' => $formCriteriaRecipeForm,
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
     * Display the recipe in parameter
     */
    public function displayAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $id = $this->params()->fromRoute('id', 1);
                $em = $this->getEntityManager();

                $form = new RecipeForm($em);

                //read the recipe details
                $recipe = $em->getRepository('Warehouse\Entity\Recipe')->findByRecipeId($id);
                $form->bind($recipe[0]);

                $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
                if ($this->request->isPost()) {
                    $data = $this->params()->fromPost();
                    if (isset($data['backToList']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'list']);
                        return $this->getResponse();
                    }
                    if (isset($data['add']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'add']);
                        return $this->getResponse();
                    }
                    if (isset($data['update']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'edit', 'id' => $id]);
                        return $this->getResponse();
                    }
                    if (isset($data['check']) == 1) {
                        $this->checkAvailability($recipe[0]);
                    }
                    if (isset($data['delete']) == 1) {


                    }
                }

                //read the default recipe photo stored in recipeattachment table
                $AttachmentEntity = $em->getRepository('Warehouse\Entity\RecipeAttachment')->findByRecipeIdDefaultPhoto($id);
                if ($AttachmentEntity) {
                    $Attachment = $AttachmentEntity[0];
                } else {
                    $Attachment = null;
                }

                $viewModel->setVariables([
                    'recipe' => $recipe[0],
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
     * Check the ingredients availability of the recipe in parameter
     * @param Recipe $recipe
     */
    private function checkAvailability(Recipe $recipe){
        $em = $this->getEntityManager();
        foreach($recipe->getIngredients() as $ing){
            $stockMergement = $em->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($ing->getStockmergement()->getId());
            $quantity = $stockMergement[0]->getNetquantity();
            if ($quantity < $ing->getQuantity()) {
                $ing->setAvailability(EnumAvailability::NotOnStock);
            }
            else{
                    $ing->setAvailability(EnumAvailability::OnStock);
                }
        }
    }

    /**
     * Edit the recipe in parameter
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
                $recipes = $em->getRepository('Warehouse\Entity\Recipe')->findByRecipeId($id);
                $recipe = $recipes[0];

                $form = new RecipeForm($em);
                $form->setBindOnValidate(false);
                $form->bind($recipe);

                foreach($recipe->getIngredients() as $ing){
                    //load stock value for each ingredients
                    foreach($form->get('recipe')->get('ingredients') as $fing){
                        if ($ing->getId() == $fing->get('id')->getValue()){
                            $fing->get('merge_id')->setValue($ing->getStockmergement()->getId());
                            break;
                        }
                    }
                    //load unit value for each ingredients
                    foreach($form->get('recipe')->get('ingredients') as $fing){
                        if ($ing->getId() == $fing->get('id')->getValue()){
                            $fing->get('unit_id')->setValue($ing->getUnit()->getId());
                            break;
                        }
                    }
                }
                $form->get('recipe')->get('category_id')->setAttributes([
                        'value' => $recipe->getCategory()->getId()
                    ]
                );

                $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
                if ($request->isPost()) {
                    $data = $this->params()->fromPost();
                    if (isset($data['backToList']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'list']);
                        return $this->getResponse();
                    }
                    if (isset($data['cancel']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $id]);
                        return $this->getResponse();
                    }
                    if (isset($data['update']) == 1) {
                        $form->setData($data);
                        if ($form->isValid()) {
                            $recipe->setDescription($form->get('recipe')->get('description')->getValue());
                            $recipe->setServes($form->get('recipe')->get('serves')->getValue());
                            $date = new \DateTime($form->get('recipe')->get('preparationTime')->getValue());
                            $recipe->setPreparationTime($date);
                            $date = new \DateTime($form->get('recipe')->get('totalTime')->getValue());
                            $recipe->setTotalTime($date);
                            $recipe->setNote($form->get('recipe')->get('note')->getValue());
                            $category = $em->getRepository('Warehouse\Entity\RecipeCategory')->findBySettingId($form->get('recipe')->get('category_id')->getValue(),EnumTableSettings::RECIPE_CATEGORY);
                            $recipe->setCategory($category[0]);

                            $ingredients = $this->getEntityManager()->getRepository('Warehouse\Entity\Ingredient')->findByRecipeId($id);
                            foreach ($ingredients as $ing) {
                                $em->remove($ing);
                                $em->flush();
                            }

                            if (isset($data['recipe']['ingredients'])) {
                                foreach ($data['recipe']['ingredients'] as $ing) {
                                    if ($ing['merge_id'] !== "") {
                                        $ingredient = new Ingredient();
                                        $ingredient->setDescription($ing['description']);
                                        $ingredient->setSequence($ing['sequence']);
                                        $ingredient->setQuantity($ing['quantity']);
                                        $unit = $em->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($ing['unit_id'], EnumTableSettings::MEASUREUNIT);
                                        $ingredient->setUnit($unit[0]);
                                        $ingredient->setRecipe($id);
                                        $stockMergement = $em->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($ing['merge_id']);
                                        $ingredient->setStockmergement($stockMergement[0]);
                                        $recipe->addIngredients($ingredient);
                                    }
                                }
                            }

                            $instructions = $this->getEntityManager()->getRepository('Warehouse\Entity\Instruction')->findByRecipeId($id);
                            foreach ($instructions as $ins) {
                                $em->remove($ins);
                                $em->flush();
                            }

                            if (isset($data['recipe']['instructions'])) {
                                foreach ($data['recipe']['instructions'] as $ins) {
                                    if ($ins['description'] !== "") {
                                        $instruction = new Instruction();
                                        $instruction->setDescription($ins['description']);
                                        $instruction->setSequence($ins['sequence']);
                                        $instruction->setRecipe($id);
                                        $recipe->addInstructions($instruction);
                                    }
                                }
                            }
                            $em->persist($recipe);
                            $em->flush();
                        }
                        else {
                            var_dump('form non valid');
                            var_dump($form->getMessages());
        //                    die("stop");
                        }
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $id]);
                        return $this->getResponse();

                    }
                }

                $viewModel->setVariables([
                    'recipe' => $recipe,
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
     * Add a new recipe action
     */
    public function addAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $viewModel = new ViewModel();

                $em = $this->getEntityManager();

                $recipe = new Recipe();
                $recipeForm = new RecipeForm($em);
                $recipeForm->bind($recipe);

                $recipeForm->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
                if ($this->request->isPost()) {
                    $recipeForm->setData($this->request->getPost());

                    $data = $this->params()->fromPost();
                    if (isset($data['backToList']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'list']);
                        return $this->getResponse();
                    }
                    if (isset($data['create']) == 1) {
                        $recipeForm->setData($data);

                        if ($recipeForm->isValid()) {
                            $category = $em->getRepository('Warehouse\Entity\RecipeCategory')->findBySettingId($recipeForm->get('recipe')->get('category_id')->getValue(),EnumTableSettings::RECIPE_CATEGORY);
                            $recipe->setCategory($category[0]);
                            $em->persist($recipe);
                            $em->flush();
                            $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'edit', 'id' => $recipe->getId()]);
                            return $this->getResponse();
                        }
                        else {
                          //  var_dump('form non valid');
                          //  var_dump($recipeForm->getMessages());
                        }
                    }
                }

                $viewModel->setVariables([
                        'recipeForm' => $recipeForm
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
     * From the article list, add 1 quantity of the article to the shopping list table
     */
    public function shoppinglistAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $em = $this->getEntityManager();
                $from = $this->params()->fromQuery('from',null);
                $recipeId = $this->params()->fromRoute('id', 0);

                $recipeEntity = $em->getRepository('Warehouse\Entity\Recipe')->findByRecipeId($recipeId);
                $recipe = $recipeEntity[0];
                $this->checkAvailability($recipe);
                foreach ($recipe->getIngredients() as $ing) {
                    if ($ing->getAvailability()==EnumAvailability::NotOnStock ){
                        $stockMergementEntity = $em->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($ing->getStockmergement()->getId());
                        $stockMergement = $stockMergementEntity[0];
                        $shoppingList = new ShoppingList();
                        $quantity = $ing->getQuantity();
                        $shoppingList->setDescription($stockMergement->getDescription());
                        $section = $em->getRepository('Warehouse\Entity\Section')->findBySettingId($stockMergement->getSection()->getId(),'section');
                        $shoppingList->setSection($section[0]);
                        $supplier = $em->getRepository('Warehouse\Entity\Supplier')->findBySettingId($stockMergement->getSupplier()->getId(),'supplier');
                        $shoppingList->setSupplier($supplier[0]);
                        $shoppingList->setPriority(EnumPriority::PRIORITY_MAJOR);
                        $shoppingList->setStockmergement($stockMergement);
                        $shoppingList->setUnit($ing->getUnit());
                        $area = $em->getRepository('Warehouse\Entity\Area')->findBySettingId($stockMergement->getArea()->getId(),'area');
                        $shoppingList->setArea($area[0]);
                        $shoppingList->setRecipe($recipe);
                        $shoppingList->setQuantity($quantity);
                        $shoppingList->setSendtostock(1);
                        $shoppingList->setStatus(EnumShoppingListStatus::SHOPPING_LIST_STATUS_NEW_TO_BUY);
                        $em->persist($shoppingList);
                    }
                }
                $em->flush();
                if ($from == 'recipe') {
                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $recipeId]);
                }
                if ($from == 'list'){
                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'list']);
                }
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
     * From the article list, add -1 quantity of the article to the stock interface list table
     */
    public function stockinterfaceAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $em = $this->getEntityManager();
                $from = $this->params()->fromQuery('from',null);
                $recipeId = $this->params()->fromRoute('id', 0);

                $recipeEntity = $em->getRepository('Warehouse\Entity\Recipe')->findByRecipeId($recipeId);
                $recipe = $recipeEntity[0];
                $this->checkAvailability($recipe);
                foreach ($recipe->getIngredients() as $ing) {
                    $stockMergementEntity = $em->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($ing->getStockmergement()->getId());
                    $stockMergement = $stockMergementEntity[0];
                    $stockInterface = new StockInterface();
                    $stockInterface->setDescription($stockMergement->getDescription());
                    $stockInterface->setMerge($stockMergement);
                    $stockInterface->setSens(EnumStockMovementType::MOVEMENT_SHOP_REMOVE);
                    $stockInterface->setQuantity($ing->getQuantity());
                    $stockInterface->setUnit($ing->getUnit());

                    $stockPrefered = $this->getEntityManager()->getRepository('Warehouse\Entity\Stock')->findByPreferedMergeId($stockMergement->getId());
                    $stockInterface->setStock($stockPrefered[0]);

                    $qtyU = 0;
                    switch ($ing->getUnit()->getId()) {
                        case EnumUnit::UNIT_GRAM:
                            $qtyU = $ing->getQuantity();
                            break;
                        case EnumUnit::UNIT_PIECE:
                            if ($stockMergement->getEqpiece() != 0)
                                $qtyU = $ing->getQuantity() * $stockMergement->getEqpiece();
                            break;
                        case EnumUnit::UNIT_TABLESPOON:
                            if ($stockMergement->getEqtblsp() != 0)
                                $qtyU = $ing->getQuantity() * $stockMergement->getEqtblsp();
                            break;
                        case EnumUnit::UNIT_COFFEESPOON:
                            if ($stockMergement->getEqcofsp() != 0)
                                $qtyU = $ing->getQuantity() * $stockMergement->getEqcofsp();
                            break;
                        case EnumUnit::UNIT_TEASPOON:
                            if ($stockMergement->getEqteasp() != 0)
                                $qtyU = $ing->getQuantity() * $stockMergement->getEqteasp();
                            break;
                        case EnumUnit::UNIT_PINCH:
                            if ($stockMergement->getEqpinch() != 0)
                                $qtyU = $ing->getQuantity() * $stockMergement->getEqpinch();
                            break;
                        case EnumUnit::UNIT_MILLILITER:
                            if ($stockMergement->getEqpiece() != 0)
                                $qtyU = $ing->getQuantity() / $stockMergement->getEqpiece();
                            break;
                    }

                    $unit = $this->getEntityManager()->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId(EnumUnit::UNIT_PIECE, EnumTableSettings::MEASUREUNIT);
                    $stockInterface->setUnittointegrate($unit[0]);

                    if ($ing->getUnit()->getId() == EnumUnit::UNIT_PIECE) {
                        $stockInterface->setQuantitytointegrate($ing->getQuantity());
                    } else {
                        if ($stockMergement->getEqpiece() != 0) {
                            $qty = $qtyU / $stockMergement->getEqpiece();
                            if ($qty < 1) $qty = 1;
                        } else {
                            //$qty = $ing->getQuantity();
                            $qty = 1;
                        }
                        $stockInterface->setQuantitytointegrate($qty);
                    }

                    $this->getEntityManager()->persist($stockInterface);
                    $this->getEntityManager()->flush();
                }
                $em->flush();
                if ($from == 'recipe') {
                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $recipeId]);
                }
                if ($from == 'list'){
                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'list']);
                }
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
     * Delete the recipe in parameter
     */
    public function deleteAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {



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
     * Generate pdf file with the receipe content
     */
    public function generatepdfAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                $recipeId = $this->params()->fromRoute('id', 0);
                $em = $this->getEntityManager();
                $recipeEntity = $em->getRepository('Warehouse\Entity\Recipe')->findByRecipeId($recipeId);
                $recipe = $recipeEntity[0];

                $pdf = new FPDF\PDF();
                $pdf->SetAutoPageBreak(true,15);
                $pdf->AddFont('Verdana','','Verdana.php');
                $pdf->AddPage();
                $pdf->SetFont('Verdana','',22);

                $AttachmentEntity = $em->getRepository('Warehouse\Entity\RecipeAttachment')->findByRecipeIdDefaultPhoto($recipeId);
                if ($AttachmentEntity) {
                    $Attachment = $AttachmentEntity[0];
                   $pdf->Image($_SERVER["DOCUMENT_ROOT"] .'/'. $Attachment->getPath().$Attachment->getFileName(),15,30,60);
                } else {
                    $Attachment = null;
                }

                $wrapText = $pdf->GetWrapText('TITLE',ucwords($recipe->getDescription()));
                for($i=0;$i<count($wrapText);$i++){
                    $pdf->ln();
                    $pdf->SetX(85);
                    $pdf->Cell(120,10,utf8_decode($wrapText[$i]),0,0,'L');
                }

                $pdf->AddFont('Verdana Italic','I','Verdana Italic.php');
                $pdf->SetFont('Verdana Italic','I',10);
                $pdf->ln();
                $pdf->SetX(85);
                $pdf->Cell(120,5,'Category: '.utf8_decode(strtoupper($recipe->getCategory()->getDescription())).' - Serves: '.$recipe->getServes(),0,0,'L');
                $pdf->ln();
                $pdf->SetX(85);
                $prepTime = '';
                $totTime = '';
                if ($recipe->getPreparationTime()->format('H') != 00)
                    $prepTime = $recipe->getPreparationTime()->format('H').'h';
                if ($recipe->getPreparationTime()->format('i') != 0)
                    $prepTime = $prepTime.$recipe->getPreparationTime()->format('i').'min';
                if ($recipe->getTotalTime()->format('H') != 00)
                    $totTime = $recipe->getTotalTime()->format('H').'h';
                if ($recipe->getTotalTime()->format('i') != 0)
                    $totTime = $totTime.$recipe->getTotalTime()->format('i').'min';
                $pdf->Cell(120,5,'Preparation Time: '.$prepTime.' - Total Time: '.$totTime,0,0,'L');

                $pdf->ln();
                $pdf->ln();
                $pdf->SetX(85);
                $pdf->AddFont('Courier New Italic','I','Courier New Italic.php');
                $pdf->SetFont('Courier New Italic','I',12);
                $pdf->Cell(120,5,'Ingredients:',0,0,'L');
                $pdf->setY($pdf->GetY()+3);
                $pdf->SetFont('Courier New Italic','I',10);
                foreach($recipe->getIngredients() as $ingredient) {
                    if ($ingredient->getQuantity() == round($ingredient->getQuantity(),0)){
                        $quantity = round($ingredient->getQuantity(),0);
                    }
                    else {
                        $quantity = $ingredient->getQuantity();
                    }
                    $pdf->SetX(85);
                    if ($ingredient->getDescription() == "") {
                        $ing = '- '.$quantity.' '.$ingredient->getUnit()->getUnit().' '. $ingredient->getStockmergement()->getDescription();
                    } else {
                        $ing = '- '.$quantity.' '.$ingredient->getUnit()->getUnit().' '. $ingredient->getStockmergement()->getDescription().' - '.$ingredient->getDescription();
                    }
                    $wrapText = $pdf->GetWrapText('INGREDIENT',$ing);
                    for($i=0;$i<count($wrapText);$i++){
                        if ($i<count($wrapText)) $pdf->ln();
                        $pdf->SetX(85);
                        $pdf->Cell(120,5,utf8_decode($wrapText[$i]),0,0,'L');
                    }
                }

                if ($pdf->GetY() < 120) $pdf->setY(120);
                $pdf->SetFont('Verdana','',14);
                $pdf->ln();
                $pdf->ln();
                $pdf->Cell(0,5,'Preparation:',0,0,'L');
                $pdf->setY($pdf->GetY()+3);
                $pdf->SetFont('Verdana','',10);
                foreach($recipe->getInstructions() as $instruction) {
                     $wrapText = $pdf->GetWrapText('PREPARATION',$instruction->getSequence().' - '.$instruction->getDescription());
                     for($i=0;$i<count($wrapText);$i++){
                         $pdf->ln();
                         $pdf->Cell(0,5,utf8_decode($wrapText[$i]),0,0,'L');
                     }
                     $pdf->setY($pdf->GetY()+3);

                }

                $pdf->SetFont('Verdana','',14);
                $pdf->ln();
                $pdf->ln();
                $pdf->Cell(0,5,'Note / Suggestion:',0,0,'L');
                $pdf->setY($pdf->GetY()+3);
                $pdf->SetFont('Verdana','',10);
                $wrapText = $pdf->GetWrapText('NOTE',ucwords($recipe->getNote()));
                for($i=0;$i<count($wrapText);$i++){
                    $pdf->ln();
                    $pdf->Cell(0,5,utf8_decode($wrapText[$i]),0,0,'L');
                }
                $pdf->Output('F',$_SERVER["DOCUMENT_ROOT"] . '/recipes/'.str_replace('\'',' ',$recipe->getDescription()).'.pdf',true);

                $file = '<a href="#" onclick="OpenRLink(\'../../../../recipes/'.str_replace('\'',' ',$recipe->getDescription()).'.pdf\');">'.str_replace('\'',' ',$recipe->getDescription()).'.pdf</a>';
             //   $file = $file . ' <input name="sendmail" type="button" onclick="sendmail(\'../../../../recipes/'.str_replace('\'',' ',$recipe->getDescription()).'.pdf\');" id="sendmailpdf" value="Send by email">';
                $response = $this->getResponse();
                $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
                $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" =>'', "file" => $file)));
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