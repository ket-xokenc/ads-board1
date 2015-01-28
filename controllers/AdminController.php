<?php
use application\core\BaseController;
use application\classes\Registry;

class AdminController extends BaseController
{
    private $db;
    private $admin;

    public function __construct($request)
    {
        $this->db = Registry::get('database');
        parent::__construct($request);
        $this->admin = new Admin();
    }

//    public function regiserAction()
//    {
//        if ($this->getRequest()->isPost()) {
//          $reg = $this->admin->register();
//        }
//        $this->render('admin/login_adm', ['error' => $reg]);
//    }

    public function panelAction()
    {
//        if ($this->admin->isAdmin()) {
            $this->render('admin/panel', ['menu' => $this->admin->menu(), 'row' => $this->admin->panel()]);
//        } else
//            $this->render('admin/login_adm', ['error' => '<strong>Для входа вам нужны права администратора!!!</strong>']);
    }

    public function banAction()
    {
        $par = $this->getRequest()->getParams();
        $par = (int)(array_shift($par));
        $this->admin->banUnban('banned', $par);
        header("Location: /admin");
    }

    public function unbanAction()
    {
        $par = $this->getRequest()->getParams();
        $par = (int)(array_shift($par));
        $this->admin->banUnban('confirmed', $par);
        header("Location: /admin");
    }

    public function searchAction()
    {
        if (isset($_POST['search']) && iconv_strlen($_POST['search']) > 1) {
            $search = $_POST['search'];
            $search = preg_replace("#[^0-9a-z]#i", "", $search);
            $finder = $this->admin->search($search);
            if (empty($finder)) {
                echo '<p>попробуйте другое ключевое слово</p><br />';
            } else echo json_encode($finder);
        }
    }

    public function categoriesAction()
    {
        $this->render('admin/categories', ['items' => $this->admin->treeCategory(), 'menu' => $this->admin->menu(), 'categories' => $this->admin->getCategoriesPid()]);
    }

    public function personal_pageAction()
    {
        $id = $this->getRequest()->getParams();
        $id = (int)(array_shift($id));
        $plan = $this->admin->get_plan($id);
        foreach ($plan as $p) {
            $this->render('admin/personal_page', ['row' => $this->admin->get_user($id), 'plan' => $p, 'menu' => $this->admin->menu(), 'comment' => $this->admin->get_comments($id)]);
        }
    }

    public function addCatAction()
    {
        if (iconv_strlen($_POST['name']) > 1 && iconv_strlen($_POST['description']) > 1) {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $pid = (int)$_POST['pid'];
            if (isset($_POST['checkbox']) && $_POST['checkbox'] == 1) {
                $this->admin->addCategory($name, $description, $pid);
                header("Location: /categories");
            } else
                $this->admin->addCategory($name, $description);
            header("Location: /categories");
        }
    }

    public function showAction()
    {
        $par = $this->getRequest()->getParams();
        $par = (int)(array_shift($par));
        $this->render('admin/show', ['menu' => $this->admin->menu(), 'row' => $this->admin->show($par)]);
    }

    public function cat_propertyAction()
    {
        $par = $this->getRequest()->getParams();
        $par = (int)(array_shift($par));
        $property_all = $this->admin->properties();
        $this->render('admin/category_properties', ['row' => $par, 'menu' => $this->admin->menu(), 'property_all' => $property_all, 'cat_id' => $par, 'property' => $this->admin->cat_property($par)]);
    }

    public function add_propertyAction()
    {

        if (isset($_POST['per_cat']) && !(empty($_POST['add']))) {
            $arr = $_POST['add'];
            $this->admin->addPropertyToCat($arr);
            $id = (int)array_shift($arr);
            header("Location: /categories/property/{$id}");
        }
        if (isset($_POST['add_cat']) && iconv_strlen($_POST['property_name']) > 1 && iconv_strlen($_POST['property_type']) > 1) {
            $id = array_keys($_POST['id']);
            $id = array_shift($id);
            $property_name = $_POST['property_name'];
            $property_type = $_POST['property_type'];
            $property_values = json_encode($_POST['property_values']);
            $this->admin->addProperty($property_name, $property_type, $property_values);

//            $this->db->insert('properties', ['name' => $property_name, 'params' => $property_values, 'type' => $property_type]);
            header("Location: /categories/property/{$id}");
        }
        if (isset($_POST['delete'])) {
            $id = intval($_POST['delete']);
            $this->admin->deletePropertyCat($id);
        } else
            echo "<h1>Проверте правильность запроса и повторите попытку</h1>";
    }
}