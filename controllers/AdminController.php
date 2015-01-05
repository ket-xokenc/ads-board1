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
        $user = new Users();
        $id = $user->get();

        $finder = $this->db->query("SELECT * FROM users ORDER BY date_create", array());
        $this->render('admin/panel', ['row'=>$finder]);
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

    function searchAction()
    {
        $search = $_POST['search'];
        $finder = $this->db->query("SELECT * FROM users WHERE login LIKE '%$search%'", array('login'=>$search));
        foreach ($finder as $k) {
            echo "Логин:  ".$k['login'].'<br />';
            echo "Почта:  ".$k['email'].'<br />';
            echo "Статус:  ".$k['status'].'<br />';

        }}
    function imgAction()
    {
        if(isset($_FILES))
        {
            $name = $_FILES['upload']['name'];
            copy($_FILES['upload']['tmp_name'], 'images/1.jpeg');
            header('Location: http://site.com/admin');
        }else echo "BARAN";
    }
    function categoriesAction()
    {
        $row = $this->db->query("SELECT categories.id, categories.name, COUNT(ads.category_id) AS count1
                                    FROM categories LEFT JOIN ads ON(categories.id=ads.category_id) GROUP BY categories.id", []);
        $this->render('admin/categories', ['row'=>$row]);
    }
    function addCatAction()
    {
        $name = $_POST['name'];
        $desc = $_POST['desc'];

        $this->db->insert('categories', ['name'=>$name, 'description'=>$desc]);
        header('Location: http://site.com/categories');
    }
    function showAction()
    {
        $param = $this->getRequest()->getParams();
        $b = array_shift($param); $b = (int)$b;
        $row = $this->db->query("SELECT title, text,login, status, ads.date_create
                                    FROM ads LEFT JOIN users ON(ads.user_id=users.id) WHERE ads.category_id='$b'", []);
        $this->render('admin/show', ['row'=>$row]);
    }

}