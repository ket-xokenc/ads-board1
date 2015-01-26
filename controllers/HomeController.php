<?php
use application\core\BaseController;
use application\classes\Database;
use application\classes\Paginator;

class HomeController extends BaseController
{
    public function indexAction()
    {
        $data = [];

        $user = new Users();
        $dataUser = $user->get();
        $ads = new Ads(new Category());
        $dbinfo = $ads->getAllAds();
//        print_r($dbinfo);exit;

        $title = 'Home';
        $paginator=new Paginator(['ads'=>$ads,'users'=>$user],2,3);

        $this->render('site/home', array('title' => $title, 'user' => $dataUser, 'dbinfo' => $dbinfo, 'paginator' => $paginator));
    }

    public function addAction()
    {
        echo 'kjhsfd';
    }

    public function searchAction()
    {
        if (isset($_POST['search'])) {
            $ads = new Ads(new Category());
            $dbinfo = $ads->getAdsByText($_POST['search']);
            if (!empty($dbinfo)) {
                foreach ($dbinfo as $vals) {
                    echo "<li class=\"list-group-item\"><a href=\"/show/{$vals['ads_id']}\">{$vals['ads_title']} </a></li>";
                }
            }
            else echo "<li class=\"list-group-item\">Нет результатов</li>";
        }
    }

    public function staticPageAction()
    {
        $user = new Users();
        $data = $user->get();
        $page = current($this->getRequest()->getParams());
        $this->render("site/$page", ['user' => $data]);

    }
}