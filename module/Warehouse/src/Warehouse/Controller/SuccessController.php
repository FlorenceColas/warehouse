<?php
/**
 * User: FlorenceColas
 * Date: 24/01/2017
 * Version: 1.00
 * SuccessController: welcome page after valid authentication:
 *      - index: welcome page
 *      - changepassword: change the user password
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Controller;

use Warehouse\Enum\EnumUserAccess;
use Warehouse\Form\PasswordForm;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SuccessController extends AbstractActionController
{
    protected $authservice;
    protected $storage;
    protected $entityManager;
    protected $audittrailservice;

    /**
     * Welcome page
     */
    public function indexAction()
    {
        $authService = $this->getAuthService();
        if($authService->hasIdentity()) {
            if ($authService->sessionIsValid()) {
                $storage = $this->getAuthService()->getStorage();
                if ($storage->getAccess() == EnumUserAccess::UER_ACCESS_VISITOR)
                    $this->layout('layout/visitor');
                $viewModel = new ViewModel();
                $viewModel->setVariables([
                    'name' => $storage->getName(),
                    'access' => $storage->getAccess(),
                    'id' => $storage->getId(),
                    'flashmessages' => $this->flashmessenger()->getMessages(),
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
     * Change the user password action - link on welcome page
     */
    public function changepasswordAction()
    {
        $id = $this->params()->fromRoute('id', 1);
        $authService = $this->getAuthService();
        if($authService->hasIdentity()) {
            if ($authService->sessionIsValid()) {
                $viewModel = new ViewModel();

                $em = $this->getEntityManager();

                $form = new PasswordForm($em);

                //read the job details
                $user = $em->getRepository('Warehouse\Entity\User')->findByUserId($id);
                $form->bind($user[0]);
                $oldPwd = $user[0]->getPassword();

                $form->setAttribute('action', $this->getRequest()->getUri()->__toString());
                $message = array();
                if ($this->request->isPost()) {
                    $data = $this->params()->fromPost();
                    if (isset($data['cancel']) == 1) {
                        $this->redirect()->toRoute('warehouse/default', ['controller' => 'success', 'action' => 'index']);
                        return $this->getResponse();
                    } elseif (isset($data['update']) == 1) {
                        $form->setData($data);
                        if ($form->isValid()) {
                            //control old password
                            if (md5($form->get('password')->get('password')->getValue()) == $oldPwd) {
                                //control 2 fields new password equals
                                if (md5($form->get('password')->get('newpassword1')->getValue()) == md5($form->get('password')->get('newpassword2')->getValue())) {
                                    $user[0]->setPassword(md5($form->get('password')->get('newpassword1')->getValue()));
                                    $em->persist($user[0]);
                                    $em->flush();
                                    $auditTrail = $this->getAuditTrailService();
                                    $auditTrail->logEvent('User', 'Success', 'ChangePassword', 'The user '.$user[0]->getLogonName().' has changed his password');
                                    $this->flashmessenger()->addMessage("Your password has been changed.");
                                    $this->redirect()->toRoute('warehouse/default', ['controller' => 'success', 'action' => 'index']);
                                    return $this->getResponse();
                                }
                            } else {
                                $message[] = "The old password is not correct. Your password has not been changed.";
                            }
                        } else {
                            $auditTrail = $this->getAuditTrailService();
                            $auditTrail->logEvent('User', 'Success', 'ChangePassword', 'The user '.$user[0]->getLogonName().' FAILED to change his password');
                        }
                    }
                }
                $viewModel->setVariables([
                    'user' => $user[0],
                    'form' => $form,
                    'flashmessages' => $message,
                ]);

                return $viewModel;
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        }
    }



    private function setEntityManager(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->setEntityManager($this->getServiceLocator()->get('doctrine.entitymanager.orm_default'));
        }
        return $this->entityManager;
    }


    private function getAuthService()
    {
        if (! $this->authservice) {
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
}