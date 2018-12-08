<?php

namespace Auth;

use AuditTrail\AuditTrailService;
use Auth\Service\AuthService;
use Auth\Storage\MyAuthStorage;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class AuthController
 * @package Auth
 */
class AuthController extends AbstractActionController
{
    /**
     * @var AuditTrailService
     */
    protected $auditTrail;
    /**
     * @var AuthService
     */
    protected $authService;
    /**
     * @var array
     */
    protected $config;
    /**
     * @var MyAuthStorage
     */
    protected $storage;

    /**
     * @param AuditTrailService $auditTrail
     * @param AuthService $authService
     * @param MyAuthStorage $storage
     */
    public function __construct(
        AuditTrailService $auditTrail,
        AuthService $authService,
        MyAuthStorage $storage,
        array $config
    ) {
        $this->authService = $authService;
        $this->storage     = $storage;
        $this->auditTrail  = $auditTrail;
        $this->config      = $config;
    }

    /**
     * @return ViewModel
     */
    public function loginAction(): ViewModel
    {
        $message = [];

        if($this->authService->hasIdentity()) {
            if ($this->authService->sessionIsValid()) {
                return $this->redirect()->toRoute('success', [
                    'controller' => \Auth\SuccessController::class,
                    'action'     => 'index',
                ]);
            } else {
                $this->flashmessenger()->addMessage("Your session has been disconnected");
                return $this->redirect()->toRoute('login', [
                    'controller' => \Auth\AuthController::class,
                    'action'     => 'login',
                ]);
            }
        }

        $this->layout('layout/login');

        $login   = new Login();
        $builder = new AnnotationBuilder();
        $form    = $builder->createForm($login);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
            if ($form->isValid()) {
                $auth = $this->authService->authenticateUser($form->get('logonname')->getValue(),$form->get('password')->getValue(),$form->get('rememberme')->getValue());
                if ($auth['valid']) {
                    return $this->redirect()->toRoute('success', [
                        'controller' => \Auth\SuccessController::class,
                        'action'     => 'index',
                    ]);
                }
                if (isset($auth['message'])) {
                    foreach($auth['message'] as $msg) {
                        $message = $msg;
                    }
                }
            }
        }

        return new ViewModel([
            'form'          => $form,
            'messages'      => $message,
            'flashmessages' => $this->flashmessenger()->getMessages(),
        ]);
    }

    /**
     * @return ViewModel
     */
    public function loginhelpAction(): ViewModel
    {
        $this->layout('layout/loginhelp');

        $message = [];

        $form = new ContactForm();
        $form->setInputFilter(new ContactFilter());

        $form->get('title')->setValue("Subject: Need help to login to Warehouse Portal");
        $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['back']) == 1) {
                $this->redirect()->toRoute('login', [
                    'controller' => \Auth\AuthController::class,
                    'action'     => 'login',
                ]);
                return $this->getResponse();
            } elseif (isset($data['send']) == 1) {
                $form->setData($data);
                if ($form->isValid()) {
                    $this->sendMail($form->get('email')->getValue(), $form->get('title')->getValue(), $form->get('message')->getValue());
                    $this->auditTrail->logEvent('Login', 'Auth', 'LoginHelp', 'Mail Subject:"Need help to login" sent from '.$form->get('name')->getValue().' - email address: '.$form->get('email')->getValue());
                    $this->flashmessenger()->addMessage("Your message has been sent.");
                    return $this->redirect()->toRoute('login', [
                        'controller' => \Auth\AuthController::class,
                        'action'     => 'login',
                    ]);
                }
            }
        }

        return new ViewModel([
            'form'      => $form,
            'messages'  => $message,
        ]);
    }

    /**
     * @return \Zend\Http\Response
     */
    public function logoutAction()
    {
        $this->authService->logOut();
        return $this->redirect()->toRoute('login', [
            'controller' => \Auth\AuthController::class,
            'action'     => 'login',
        ]);
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $body
     */
    protected function sendMail(string $to = '', string $subject = 'Need help to login', string $body = 'Need help to login'): void
    {
        $options   = new SmtpOptions($this->config['mail']['smtpOptions']);
        $transport = new Smtp($options);

        $message = new Message();

        $message->setFrom($this->config['mail']['from']);

        if ('' === $to) {
            $message->addTo($this->config['mail']['to']);
        } else {
            $message->addTo($to);
        }
        $message->setSubject($subject);

        $message->setEncoding("UTF-8");
        $message->setBody($body);

        $transport->send($message);
    }
}
