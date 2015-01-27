<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController
{
    protected $userTable;

    public function indexAction()
    {
        $users = $this->getUserTable()->fetchAll();
        return new ViewModel(['users' => $users]);
    }

    public function editAction()
    {
       // $user = $this->getUserTable()->getUser($this->params()->fromRoute('id'));
      /*  $form = $this->getServiceLocator()->get('UserEditForm');

        if ($this->request->isPost()) {
            $post = $this->request()->getPost();
            $user = $this->getUserTable()->getUser($post->id);
            $form->bind($user);
            $form->setData($post);
            if (!$form->isValid()) {
                $viewModel = new ViewModel(['error' => true, 'form' => $form]);
                $viewModel->setTemplate('users/users/edit-user');
                return $viewModel;
            }
            $this->getUserTable()->saveUser($user);
            $viewModel = new ViewModel(['form' => $form]);
            $viewModel->setTemplate('users/users/edit-user');
            return $viewModel;
        }
        $form->bind($user);*/
        $viewModel = new ViewModel();
        $viewModel->setTemplate('admin\admin\edit-user');
        return $viewModel;

    }

    public function getUserTable()
    {
        if (!$this->userTable) {
            $this->userTable = $this->getServiceLocator()->get('Users\Model\UsersTable');
        }
        return $this->userTable;
    }

}