<?php
use application\core\BaseController;
use application\classes\Session;
use application\classes\Registry;

class AdminController extends BaseController
{
    private $db;
    public function __construct($request)
    {
        $this->db = Registry::get('database');
        parent::__construct($request);
    }
    function panelAction()
    {
        $admin = $this->db->isAdmin('users');
        $this->render('admin/panel', ['row'=>$admin]);
    }
    function banAction()
    {
        $par = $this->getRequest()->getParams();
        foreach ($par as $k) {
            $k = (int)$k;
            $this->db->update('users', ['status' => 'banned'], ['id' => $k]);
            header("Location: http://".$_SERVER['SERVER_NAME']."/admin");
        }
    }
    function unbanAction()
    {
        $par = $this->getRequest()->getParams();
        foreach ($par as $k) {
            $k = (int)$k;
            $this->db->update('users', ['status' => 'registered'], ['id' => $k]);
            header("Location: http://".$_SERVER['SERVER_NAME']."/admin");
        }
    }
    function showAction()
    {
        $par = $this->getRequest()->getParams();
        foreach ($par as $k) {
            $k = (int)$k;
            $row = $this->db->fetchAll('ad', ['title', 'text'], ['id_user'=>$k]);
            $this->render('admin/show', ['row'=>$row]);
        }
    }
    function searchAction()
    {
        $admin = new Admin();
        $search = $_POST['search'];
        $finder = $this->db->search($search);
        $this->render('admin/search', ['row'=>$finder]);
    }
}