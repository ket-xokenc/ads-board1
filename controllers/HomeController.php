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
        $title = 'Домашняя страница';
        $this->render('site/home', array('title' => $title, 'user' => $data));
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
    public function searchAction()
    {
        if(isset($_GET['search']) && iconv_strlen($_GET['search'])>3){
            $search = $_GET['search'];
            $finder = $this->db->query("SELECT users.login, categories.name, ads.title FROM ads
                                                      LEFT JOIN users ON(ads.user_id=users.id)
                                                      LEFT JOIN categories ON(categories.id=ads.category_id)
                                                      WHERE login LIKE '%$search%'
                                                        OR categories.name LIKE '%$search%'
                                                     OR title LIKE '%$search%'", array('login'=>$search));
            var_dump($finder);}
        else
            header('Location: http://site.com');
    }
}