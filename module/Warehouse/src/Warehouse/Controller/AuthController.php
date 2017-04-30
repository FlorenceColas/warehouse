<?php
/**
 * User: FlorenceColas
 * Date: 29/04/2017
 * Version: 1.00
 * AuthController: manage the user authentication. It contains the following actions:
 *      - login: login form
 *      - authenticate: user authentication
 *      - logout: logout form
 *      - loginhelp: help to login form
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Controller;

use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\AnnotationBuilder;

use Warehouse\Form\ContactForm;
use Warehouse\Filter\ContactFilter;
use Warehouse\Model\Login;
use Zend\View\Model\ViewModel;

class AuthController extends AbstractActionController
{
    protected $form;
    protected $entityManager;
    protected $storage;
    protected $authservice;
    protected $mailservice;
    protected $audittrailservice;

    /**
     * Login page
     */
    public function loginAction()
    {
        $message = array();

        $authService = $this->getAuthService();
        if($authService->hasIdentity()) {
            if ($authService->sessionIsValid()) {
                return $this->redirect()->toRoute('success');
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login');
            }
        }

        $viewModel = new ViewModel();
        $this->layout('layout/login');

        $form = $this->getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
            if ($form->isValid()) {
                //check user authentication
                $auth = $this->authenticate($form->get('logonname')->getValue(),$form->get('password')->getValue(),$form->get('rememberme')->getValue());
                if ($auth['valid']) {
                    return $this->redirect()->toRoute('success');
                }
                if (isset($auth['message'])) {
                    foreach($auth['message'] as $msg) {
                        $message[] = $msg;
                    }
                }
            }
        }

        //add data to the ViewModel
        $viewModel->setVariables([
            'form'      => $form,
            'messages'  => $message,
            'flashmessages' => $this->flashmessenger()->getMessages(),
        ]);
        return $viewModel;
    }

    /**
     * Need help to login - Send contact mail
     */
    public function loginhelpAction() {
        $viewModel = new ViewModel();
        $this->layout('layout/loginhelp');

        $message = array();

        $em = $this->getEntityManager();
        $form = new ContactForm($em);
        // set input filters
        $form->setInputFilter(new ContactFilter());

        $form->get('title')->setValue("Subject: Need help to login to Warehouse Portal");
        $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['back']) == 1) {
                $this->redirect()->toRoute('warehouse/default', ['controller' => 'auth', 'action' => 'login']);
                return $this->getResponse();
            } elseif (isset($data['send']) == 1) {
                $form->setData($data);
                if ($form->isValid()) {
                    //send the mail using the service
                    $mail = $this->getMailService();
                    $mail->sendMail('',$form->get('title')->getValue(),$form->get('message')->getValue(),null);

                    //traceability
                    $auditTrail = $this->getAuditTrailService();
                    $auditTrail->logEvent('Login', 'Auth', 'LoginHelp', 'Mail Subject:"Need help to login" sent from '.$form->get('name')->getValue().' - email address: '.$form->get('email')->getValue());

                    $this->flashmessenger()->addMessage("Your message has been sent.");
                    return $this->redirect()->toRoute('login');
                }
            }
        }

        //add data to the ViewModel
        $viewModel->setVariables([
            'form'      => $form,
            'messages'  => $message,
        ]);
        return $viewModel;
    }

    /**
     * Logout action
     * @return \Zend\Http\Response
     */
    public function logoutAction()
    {
        //logout
        $authService = $this->getAuthService();
        $authService->logOut();
        return $this->redirect()->toRoute('login');
    }


    private function authenticate($logon, $password, $rememberMe)
    {
        //check authentication
        $authService = $this->getAuthService();
        $authService->getAdapter()
            ->setIdentity($logon)
            ->setCredential($password);
        $result = $authService->authenticateUser($logon,$password,$rememberMe);
        return $result;
    }

    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->setEntityManager($this->getServiceLocator()->get('doctrine.entitymanager.orm_default'));
        }
        return $this->entityManager;
    }

    private function setEntityManager(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    private function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        return $this->authservice;
    }

    private function getForm()
    {
        if (! $this->form) {
            $login = new Login();
            $builder = new AnnotationBuilder();
            $this->form = $builder->createForm($login);
        }
        return $this->form;
    }

    private function getMailService()
    {
        if (! $this->mailservice) {
            $this->mailservice = $this->getServiceLocator()->get('MailService');
        }
        return $this->mailservice;
    }

    private function getAuditTrailService()
    {
        if (! $this->audittrailservice) {
            $this->audittrailservice = $this->getServiceLocator()->get('AuditTrailService');
        }
        return $this->audittrailservice;
    }
}