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
            $dbinfo = $ads->getAdsByString($_POST['search']);
            if(!empty($dbinfo)) {
                foreach($dbinfo as $vals) {
                    echo "
                        <div class=\"col-lg-12\">
                <div class=\"panel panel-primary \">
                    <div class=\"ads-style panel-heading\">
                        <h2>{$vals['ads_title']}</h2>
                    </div>
                    <div class=\"panel-body\">
                        {$vals['ads_text']}
                    </div>
                    <div class=\"ads-style panel-footer\">
                        <div class=\"container-fluid\">
                            <div class=\"row\">
                                <div class=\"col-lg-8\" style=\"padding-left:0px\">
                                    <span class=\"ads-font\">Category: </span>{$vals['categories_name']}
                                    <span class=\"ads-font\">Name: </span>{$vals['user_name']}
                                    <span class=\"ads-font\">Phone: </span>{$vals['users_phone']}
                                    <span class=\"ads-font\">Date: </span>{$vals['ads_date_create']}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                    ";
                }
            } else echo '<p>Нет результатов</p>';
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