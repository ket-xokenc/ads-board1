<?php
use application\core\BaseController;
use application\classes\Database;

class HomeController extends BaseController
{
    public function indexAction()
    {
        //$db = new \Database();
//        $db->update('users', ['login' => 'kvasenko', 'password' => '1234567'], ['login' => 'sasha', 'password' => '0000']);
        //$db->getById('29', 'users');
        //$db->insert('users', ['login' => 'alex', 'password' => '1', 'name' => 'Александр']);
//        $db->delete('users', ['id' => 30, 'login' => 'kvasenko']);
//        print_r($db->fetchAll('users', ['id', 'login', 'name'], ['login' => 'alex', 'id' => 31]));exit;
//        print_r($db->fetchRow('users', ['id', 'login', 'name'], ['id' => 32]));exit;
        //print_r($db->fetchOne('users', 'login', ['login' => 'kvasenko']));exit;
        // $db->query('DELETE from users where login = :login', [':login' => 'alex'], [':login' => 'str']);
        $data = [];

        $user = new Users();
        $data = $user->get();
        $ads = new Ads(new Category());
        $dbinfo = $ads->getAllAds();
//        print_r($dbinfo);exit;

        $title = 'Home';
        $this->render('site/home', array('title' => $title, 'user' => $data, 'dbinfo' => $dbinfo));
        //   $result = mail('kvasenko@ukr.net', 'subject', 'message');
    }

    public function addAction()
    {
        echo 'kjhsfd';
    }

    public function staticPageAction()
    {
        $user = new Users();
        $data = $user->get();
        $page = current($this->getRequest()->getParams());
        $this->render("site/$page", ['user' => $data]);
    }
}