<?php

namespace Auth;

use AuditTrail\AuditTrailService;
use Auth\Storage\MyAuthStorage;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class SuccessController
 * @package Auth
 */
class SuccessController extends AbstractActionController
{
    const UER_ACCESS_ADMIN         = 1;
    const UER_ACCESS_USER          = 2;
    const UER_ACCESS_VISITOR       = 3;

    /**
     * @var AuditTrailService
     */
    protected $auditTrailService;
    /**
     * @var DbAdapter
     */
    protected $dbAdapter;
    /**
     * @var MyAuthStorage
     */
    protected $storage;

    /**
     * @param AuditTrailService $auditTrail
     * @param MyAuthStorage $storage
     * @param DbAdapter $dbAdapter
     */
    public function __construct(
        AuditTrailService $auditTrail,
        MyAuthStorage $storage,
        DbAdapter $dbAdapter)
    {
        $this->auditTrailService = $auditTrail;
        $this->storage           = $storage;
        $this->dbAdapter         = $dbAdapter;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        if ($this->storage->getAccess() == $this::UER_ACCESS_VISITOR) {
            $this->layout('layout/visitor');
        }

        return new ViewModel([
            'name'          => $this->storage->getName(),
            'access'        => $this->storage->getAccess(),
            'id'            => $this->storage->getId(),
            'flashmessages' => $this->flashmessenger()->getMessages(),
        ]);
    }

    /**
     * @return \Zend\Stdlib\ResponseInterface|ViewModel
     */
    public function changepasswordAction()
    {
        $id = $this->params()->fromRoute('id');

        $form = new PasswordForm();

        $sql = new Sql($this->dbAdapter);
        $select = $sql->select()
            ->columns(
                [
                    'password',
                    'logonName',
                ]
            )
            ->from('user')
            ->where([
                'id' => $id,
            ]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $sqlString = $sql->buildSqlString($select);
        $user      = iterator_to_array($statement->execute());
        $oldPwd    = $user[0]['password'];

        $form->setAttribute('action', $this->getRequest()->getUri()->__toString());
        $message = [];
        if ($this->request->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['cancel'])) {
                $this->redirect()->toRoute('success', ['controller' => 'success', 'action' => 'index']);
                return $this->getResponse();
            } elseif (isset($data['update'])) {
                $form->setData($data);
                if ($form->isValid()) {
                    if (md5($form->get('password')->get('password')->getValue()) == $oldPwd) {
                        if (md5($form->get('password')->get('newpassword1')->getValue()) == md5($form->get('password')->get('newpassword2')->getValue())) {
                            $password = md5($form->get('password')->get('newpassword1')->getValue());

                            $sql    = new Sql($this->dbAdapter);
                            $update = $sql->update('user')
                                ->set([
                                    'password' => $password,
                                ])
                                ->where([
                                    'id' => $id,
                                ]);

                            $statement = $sql->buildSqlString($update);
                            $result    = $this->dbAdapter->query($statement, DbAdapter::QUERY_MODE_EXECUTE);

                            $this->auditTrailService->logEvent('User', 'Success', 'ChangePassword', 'The user '.$user[0]['logonName'].' has changed his password');
                            $this->flashmessenger()->addMessage("Your password has been changed.");
                            $this->redirect()->toRoute('success', ['controller' => 'success', 'action' => 'index']);
                            return $this->getResponse();
                        }
                    } else {
                        $message = ["The old password is not correct. Your password has not been changed."];
                    }
                } else {
                    $this->auditTrailService->logEvent('User', 'Success', 'ChangePassword', 'The user '.$user[0]['logonName'].' FAILED to change his password');
                }
            }
        }

        return new ViewModel([
            'form'          => $form,
            'messages'      => $message,
        ]);
    }
}
