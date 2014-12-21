<?php
class AdminController extends BaseController
{private $par = array();
    function __construct(){}
    function panelAction()
    {
        $admin = new Database();
        $this->par = $admin->isAdmin();
            $this->render('admin/panel', ['row'=>$this->par]);
    }
}