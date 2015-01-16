<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Users for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Users\Controller;

use Users\Form\RegisterFilter;
use Users\Form\LoginFilter;
use Users\Form\LoginForm;
use Zend\InputFilter\InputFilter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Users\Form\RegisterForm;
use Users\Model\Users;

class UsersController extends AbstractActionController
{
    protected $userTable;

    public function indexAction()
    {
        $viewModel = new ViewModel();
        return $viewModel;
    }

    public function registerAction()
    {
        $form = $this->getServiceLocator()->get('RegisterForm');

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            if (!$form->isValid()) {
                $viewModel = new ViewModel([
                    'error' => true,
                    'form' => $form
                ]);
                $viewModel->setTemplate('users/users/new-user');
                return $viewModel;
            }
            $user = new Users();
            $user->exchangeArray($form->getData());
            $this->createUser($user);
            return $this->redirect()->toRoute(NULL, ['controller' => 'Users', 'action' => 'confirm']);
        }
        $viewModel = new ViewModel();
        $viewModel->setVariable('form', $form);
        return $viewModel;
    }

    public function loginAction()
    {
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $sm = $this->getServiceLocator();
            $form = $sm->get('LoginForm');
            $form->setData($post);

            if (!$form->isValid()) {
                return new ViewModel(['form' => $form, 'error' => true]);
            }
            $auth = $this->getAuthService();
            $auth->getAdapter()->setIdentity(
                $this->request->getPost('login'))->setCredential(
                $this->request->getPost('password')
            );
            $result = $auth->authenticate();
            if ($result->isValid()) {
                $session = new Container('user');

                $user = $this->getUserTable()->getUserByLogin($post->login);
                $session->role = $user->role;
                $auth->getStorage()->write($this->request->getPost('login'));
                return $this->redirect()->toRoute(null, ['controller' => 'Users', 'action' => 'confirm']);
            }


        }
        $viewModel = new ViewModel();
        $form = new LoginForm();
        $viewModel->setVariable('form', $form);
        $viewModel->setTemplate('users/users/login');
        return $viewModel;
    }

    public function confirmAction()
    {
        $user_login = $this->getAuthService()->getStorage()->read();
        $sess = new Container('user');
        $viewModel = new ViewModel(array(
            'user_login' => $user_login
        ));
        return $viewModel;

    }

    public function createUser($data)
    {
        $this->getUserTable()->saveUser($data);
    }

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Users\Model\UsersTable');
        }
        return $this->userTable;
    }

    public function getAuthService()
    {
        $sm = $this->getServiceLocator();
        return $sm->get('UserAuthService');
    }


}