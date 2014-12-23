<?php
use application\core\BaseController;
use application\classes\Session;

class AdminController extends BaseController
{
    public function __construct($request)
    {
        parent::__construct($request);
    }
    function panelAction()
    {
        $admin = new Admin();
        $this->render('admin/panel', ['row'=>$admin->showUsers()]);
    }
    function banAction()
    {
        $admin = new Admin();
        $par = $this->getRequest()->getParams();
        foreach ($par as $k) {
            $k = (int)$k;
            $admin->ban($k);
            header("Location: http://".$_SERVER['SERVER_NAME']."/admin");
        }
    }
    function unbanAction()
    {
        $admin = new Admin();
        $par = $this->getRequest()->getParams();
        foreach ($par as $k) {
            $k = (int)$k;
            $admin->unban($k);
            header("Location: http://".$_SERVER['SERVER_NAME']."/admin");
        }
    }
    function showAction()
    {
        $admin = new Admin();
        $par = $this->getRequest()->getParams();
        foreach ($par as $k) {
            $k = (int)$k;
            $row = $admin->showAds($k);
            $this->render('admin/show', ['row'=>$row]);
        }
    }
    function searchAction()
    {
        $admin = new Admin();
        $search = $_POST['search'];
        $finder = $admin->searchUser($search);
        var_dump($finder);
    }
}