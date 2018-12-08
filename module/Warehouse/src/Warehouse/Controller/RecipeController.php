<?php
namespace Warehouse\Controller;

use Common\PdfAdapter;
use Warehouse\Entity\Ingredient;
use Warehouse\Entity\Instruction;
use Warehouse\Entity\Recipe;
use Warehouse\Entity\ShoppingList;
use Warehouse\Entity\StockInterface;
use Warehouse\Enum\EnumTableSettings;
use Warehouse\Enum\EnumUnit;
use Warehouse\Form\RecipeForm;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class RecipeController extends AbstractActionController
{
    protected $adapter;
    protected $config;
    protected $data = [];
    protected $doctrine;
    protected $pdfAdapter;
    protected $templateRenderer;

    public function __construct(
        array $config,
        $doctrine,
        DbAdapter $adapter,
        TemplateRendererInterface $templateRenderer,
        PdfAdapter $pdfAdapter
    ) {
        $this->config           = $config;
        $this->doctrine         = $doctrine;
        $this->adapter          = $adapter;
        $this->templateRenderer = $templateRenderer;
        $this->pdfAdapter       = $pdfAdapter;
    }

    public function listAction()
    {
        $recipeSession = new Container($this->config['session_containers']['recipe_search']);

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['categories'])) {
                $recipeSession->recipe_search_category = $data['categories'];
            }
        }

        $categoriesInSession = [];
        if ($recipeSession->offsetExists($this->config['session_containers']['recipe_search_category'])) {
            $categoriesInSession = $recipeSession->recipe_search_category;
        }

        $sql = new Sql($this->adapter);

        $select = $sql->select();
        $select->columns(
            [
                'category_id' => 'category_id',
                'description' => new Expression('B.description'),
            ])
            ->from([
                'A' => 'recipes',
            ])
            ->join(
                ['B' => 'category'],
                'B.id = A.category_id',
                [
                    'counter' => new Expression('COUNT(B.id)'),
                ],
                $select::JOIN_LEFT)
            ->group('category_id');

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $categories = [];
        foreach ($result as $row) {
            $categories[$row['category_id']] = [
                'checked' => in_array($row['category_id'], $categoriesInSession),
                'id'      => $row['category_id'],
                'label'   => $row['description'] . ' (' . $row['counter'] . ')',
            ];
        }

        if ($this->getRequest()->isGet()) {
            $recipes = $this->doctrine->getRepository('Warehouse\Entity\Recipe')->getFindRecipesByCriterias();
        } else {
            $data    = $this->params()->fromPost();
            $recipes = $this->doctrine->getRepository('Warehouse\Entity\Recipe')->getFindRecipesByCriterias($data);
        }

        return new ViewModel([
            'recipes'    => json_encode($recipes),
            'categories' => json_encode($categories),
            'urlthumb'   => json_encode($this->config['url']['recipe_thumb']),
        ]);
    }

    public function displayAction()
    {
        $id = $this->params()->fromRoute('id', 1);

        $form = new RecipeForm($this->doctrine);

        $recipe = $this->doctrine->getRepository('Warehouse\Entity\Recipe')->findByRecipeId($id);
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

        $attachmentEntity = $this->doctrine->getRepository('Warehouse\Entity\RecipeAttachment')->findByRecipeIdDefaultPhoto($id);
        if ($attachmentEntity) {
            $attachment = $attachmentEntity[0];
        } else {
            $attachment = null;
         }

        return new ViewModel([
            'recipe' => $recipe[0],
            'form' => $form,
            'defaultphoto' => $attachment,
            'urlthumb' => $this->config['url']['recipe_thumb'],
            'flashmessages' => $this->flashmessenger()->getMessages(),
        ]);
    }

    protected function checkAvailability(Recipe $recipe)
    {
        foreach($recipe->getIngredients() as $ing){
            $stockMergement = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($ing->getStockmergement()->getId());
            $quantity = $stockMergement[0]->getNetquantity();
            if ($quantity < $ing->getQuantity()) {
                $ing->setAvailability(\Warehouse\Controller\StockmergementController::NOT_ON_STOCK);
            } else {
                $ing->setAvailability(\Warehouse\Controller\StockmergementController::ON_STOCK);
            }
        }
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 1);

        $request = $this->getRequest();

        $recipes = $this->doctrine->getRepository('Warehouse\Entity\Recipe')->findByRecipeId($id);
        $recipe = $recipes[0];

        $form = new RecipeForm($this->doctrine);
        $form->setBindOnValidate(false);
        $form->bind($recipe);

        foreach($recipe->getIngredients() as $ing){
            //load stock value for each ingredients
            foreach($form->get('recipe')->get('ingredients') as $fing){
                if ($ing->getId() == $fing->get('id')->getValue()){
                    $fing->get('stockmergement_id')->setValue($ing->getStockmergement()->getId());
                    break;
                }
            }
            //load unit value for each ingredients
            foreach($form->get('recipe')->get('ingredients') as $fing){
                if ($ing->getId() == $fing->get('id')->getValue()){
                    $fing->get('measureunit_id')->setValue($ing->getMeasureUnit()->getId());
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
                    $category = $this->doctrine->getRepository('Warehouse\Entity\Category')->findBySettingId($form->get('recipe')->get('category_id')->getValue(),EnumTableSettings::RECIPE_CATEGORY);
                    $recipe->setCategory($category[0]);

                    $ingredients = $this->doctrine->getRepository('Warehouse\Entity\Ingredient')->findByRecipeId($id);
                    foreach ($ingredients as $ing) {
                        $this->doctrine->remove($ing);
                        $this->doctrine->flush();
                    }

                    if (isset($data['recipe']['ingredients'])) {
                        foreach ($data['recipe']['ingredients'] as $ing) {
                            if ($ing['stockmergement_id'] !== "") {
                                $ingredient = new Ingredient();
                                $ingredient->setDescription($ing['description']);
                                $ingredient->setSequence($ing['sequence']);
                                $ingredient->setQuantity($ing['quantity']);
                                $unit = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId($ing['measureunit_id'], EnumTableSettings::MEASUREUNIT);
                                $ingredient->setMeasureUnit($unit[0]);
                                $ingredient->setRecipe($id);
                                $stockMergement = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($ing['stockmergement_id']);
                                $ingredient->setStockmergement($stockMergement[0]);
                                $recipe->addIngredients($ingredient);
                            }
                        }
                    }

                    $instructions = $this->doctrine->getRepository('Warehouse\Entity\Instruction')->findByRecipeId($id);
                    foreach ($instructions as $ins) {
                        $this->doctrine->remove($ins);
                        $this->doctrine->flush();
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
                    $this->doctrine->persist($recipe);
                    $this->doctrine->flush();
                }
                else {
                    var_dump('form non valid');
                    var_dump($form->getMessages());
                }
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $id]);
                return $this->getResponse();
            }
        }

        return new ViewModel([
            'recipe' => $recipe,
            'form' => $form,
        ]);
    }

    public function addAction()
    {
        $recipe = new Recipe();
        $recipeForm = new RecipeForm($this->doctrine);
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
                    $category = $this->doctrine->getRepository('Warehouse\Entity\Category')->findBySettingId($recipeForm->get('recipe')->get('category_id')->getValue(),EnumTableSettings::RECIPE_CATEGORY);
                    $recipe->setCategory($category[0]);
                    $this->doctrine->persist($recipe);
                    $this->doctrine->flush();
                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'edit', 'id' => $recipe->getId()]);
                    return $this->getResponse();
                }
                else {

                }
            }
        }

        return new ViewModel([
            'recipeForm' => $recipeForm
        ]);
    }

    /**
     * From the article list, add 1 quantity of the article to the shopping list table
     */
    public function shoppinglistAction(){
        $from = $this->params()->fromQuery('from',null);
        $recipeId = $this->params()->fromRoute('id', 0);

        $recipeEntity = $this->doctrine->getRepository('Warehouse\Entity\Recipe')->findByRecipeId($recipeId);
        $recipe = $recipeEntity[0];
        $this->checkAvailability($recipe);
        foreach ($recipe->getIngredients() as $ing) {
            if ($ing->getAvailability()==\Warehouse\Controller\StockmergementController::NOT_ON_STOCK ){
                $stockMergementEntity = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($ing->getStockmergement()->getId());
                $stockMergement = $stockMergementEntity[0];
                $shoppingList = new ShoppingList();
                $quantity = $ing->getQuantity();
                $shoppingList->setDescription($stockMergement->getDescription());
                $section = $this->doctrine->getRepository('Warehouse\Entity\Section')->findBySettingId($stockMergement->getSection()->getId(),'section');
                $shoppingList->setSection($section[0]);
                $supplier = $this->doctrine->getRepository('Warehouse\Entity\Supplier')->findBySettingId($stockMergement->getSupplier()->getId(),'supplier');
                $shoppingList->setSupplier($supplier[0]);
                $shoppingList->setPriority(1);
//                $shoppingList->setPriority(EnumPriority::PRIORITY_MAJOR);
                $shoppingList->setStockmergement($stockMergement);
                $shoppingList->setMeasureUnit($ing->getMeasureUnit());
                $area = $this->doctrine->getRepository('Warehouse\Entity\Area')->findBySettingId($stockMergement->getArea()->getId(),'area');
                $shoppingList->setArea($area[0]);
                $shoppingList->setRecipe($recipe);
                $shoppingList->setQuantity($quantity);
                $shoppingList->setSendtostock(1);
                $shoppingList->setStatus(\Warehouse\Controller\ShoppingController::SHOPPING_LIST_STATUS_NEW_TO_BUY);
                $this->doctrine->persist($shoppingList);
            }
        }
        $this->doctrine->flush();
        if ($from == 'recipe') {
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $recipeId]);
        }
        if ($from == 'list'){
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'list']);
        }
        return $this->getResponse();
    }


    /**
     * From the article list, add -1 quantity of the article to the stock interface list table
     */
    public function stockinterfaceAction()
    {
        $from = $this->params()->fromQuery('from',null);
        $recipeId = $this->params()->fromRoute('id', 0);

        $recipeEntity = $this->doctrine->getRepository('Warehouse\Entity\Recipe')->findByRecipeId($recipeId);
        $recipe = $recipeEntity[0];
        $this->checkAvailability($recipe);
        foreach ($recipe->getIngredients() as $ing) {
            $stockMergementEntity = $this->doctrine->getRepository('Warehouse\Entity\StockMergement')->findByStockMergementId($ing->getStockmergement()->getId());
            $stockMergement = $stockMergementEntity[0];
            $stockInterface = new StockInterface();
            $stockInterface->setDescription($stockMergement->getDescription());
            $stockInterface->setStockMergement($stockMergement);
            $stockInterface->setSens(\Warehouse\Controller\ShoppingController::MOVEMENT_SHOP_REMOVE);
            $stockInterface->setQuantity($ing->getQuantity());
            $stockInterface->setMeasureUnit($ing->getMeasureUnit());

            $stockPrefered = $this->doctrine->getRepository('Warehouse\Entity\Stock')->findByPreferedMergeId($stockMergement->getId());
            $stockInterface->setStock($stockPrefered[0]);

            $qtyU = 0;
            switch ($ing->getMeasureUnit()->getId()) {
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

            $unit = $this->doctrine->getRepository('Warehouse\Entity\MeasureUnit')->findBySettingId(EnumUnit::UNIT_PIECE, EnumTableSettings::MEASUREUNIT);
            $stockInterface->setUnittointegrate($unit[0]);

            if ($ing->getMeasureUnit()->getId() == EnumUnit::UNIT_PIECE) {
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

            $this->doctrine->persist($stockInterface);
            $this->doctrine->flush();
        }
        $this->doctrine->flush();
        if ($from == 'recipe') {
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'display', 'id' => $recipeId]);
        }
        if ($from == 'list'){
            $this->redirect()->toRoute('warehouse/default', ['controller' => 'recipe', 'action' => 'list']);
        }
        return $this->getResponse();
    }

    public function deleteAction()
    {

    }

    public function generatepdfAction(){
        $recipeId = $this->params()->fromRoute('id', 0);
        $recipeEntity = $this->doctrine->getRepository('Warehouse\Entity\Recipe')->findByRecipeId($recipeId);
        $recipe = $recipeEntity[0];

        $attachmentEntity = $this->doctrine->getRepository('Warehouse\Entity\RecipeAttachment')->findByRecipeIdDefaultPhoto($recipeId);
        if ($attachmentEntity) {
            $attachment = $attachmentEntity[0];
        } else {
            $attachment = null;
        }

        $html = $this->templateRenderer
            ->render(
                'recipe::recipe', [
                'recipe' => $recipe,
                'photo'  => $attachment,
            ]);

        $this->data = $this->pdfAdapter
            ->setContent($html, $this->config['path']['tmp'])
            ->render();

        $file = $this->config['path']['recipe_public_pdf'] . '/' . $recipe->getDescription() . '.pdf';
        $result = file_put_contents($file, $this->data);

        $file = '<a href="#" onclick="OpenRLink(\'../../../../recipes/'.$recipe->getDescription().'.pdf\');"> '.$recipe->getDescription().'.pdf</a>';
        $response = $this->getResponse();
        $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json;charset=UTF-8'));
        $response->setContent(\Zend\Json\Json::encode(array("result" => "success", "msg" =>'', "file" => $file)));

        return $response;
    }
}
