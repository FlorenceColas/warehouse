<?php
/**
 * User: FlorenceColas
 * Date: 10/01/2017
 * Version: 1.00
 * MovementController: store stock movement. It contains the following actions:
 *      - add: add stock movement(s)
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *  29/04/2017 - integrate session/authentication
 */

namespace Warehouse\Controller;

use Warehouse\Entity\StockMovement;
use Warehouse\Form\StockMovementForm;

class MovementController
{
    protected $authservice;
    protected $audittrailservice;
    protected $entityManager;

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

    public function addAction(){
        if ($this->getAuthService()->hasIdentity()) {
            if ($this->getAuthService()->sessionIsValid()) {
                    $viewModel = new ViewModel();

                    $stock_id = $this->params()->fromRoute('id', 1);
                    $em = $this->getEntityManager();

                    //read the stock article details
                    $stock = $em->getRepository('Warehouse\Entity\Stock')->findByStockId($stock_id);
                   // $stock[0]

                    $stockMovement = new StockMovement();
                    $request = $this->getRequest();

                    $form = new StockMovementForm($em);
                    $form->setBindOnValidate(false);
                    $form->bind($stockMovement);

                    $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
                    if ($request->isPost()) {
                        $data = $this->params()->fromPost();


                    }

                   /* $viewModel->setVariables([
                        'stock' => $stock,
                        'form' => $form,
                    ]);*/

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

}