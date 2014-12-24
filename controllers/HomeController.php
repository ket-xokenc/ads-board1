<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
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




        $logger = new Logger('my_logger');
        $logger->pushHandler(new StreamHandler('../logs/debug.log', Logger::DEBUG, false));
        $logger->pushHandler(new StreamHandler('../logs/info.log', Logger::INFO, false));
        $logger->pushHandler(new StreamHandler('../logs/error.log', Logger::WARNING, false));
        $logger->debug('debug');
        $logger->info('info');
        $logger->err('warn');
        $logger->err('error');
echo "----------------------ERROR:\n";
print_r(file_get_contents('../logs/error.log'));
echo "---------------------INFO:\n";
print_r(file_get_contents('../logs/info.log'));
echo "----------------------DEBUG:\n";
print_r(file_get_contents('../logs/debug.log'));
        exit;





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
}