<?php
class AdminController extends BaseController
{
    function __construct(){}
    function panelAction()
    {echo 'admin';
        $text = 'Тело сайта';
        $this->render('admin/panel', ['text'=>$text]);
    }
}