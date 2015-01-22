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
        $this->admin = new Users();

    }

    public function regiserAction()
    {
        if ($this->getRequest()->isPost()) {
            $error = $this->admin->authorize();
            if (Users::isAuthorized()) {
                header('Location: /admin');
            }
        }
        $this->render('admin/login_adm', ['error' => $error]);
    }

    public function is_adm()
    {
        $array = $this->admin->get();
        if ($array['role'] == 'admin')
            return true;
        else return false;
    }

    public function panelAction()
    {
        if ($this->is_adm()) {
            $row = $this->db->query("SELECT users.id, login, status, users.phone, users.date_create, COUNT(ads.user_id) AS  caunt
                                FROM users LEFT JOIN ads ON(users.id=ads.user_id) GROUP BY users.id", []);
            $this->render('admin/panel', ['menu' => $this->menu(), 'row' => $row]);
        } else
            $this->render('admin/login_adm', ['error' => '<strong>Для входа вам нужны права администратора!!!</strong>']);
    }

    public function banAction()
    {
        $par = $this->getRequest()->getParams();
        $par = (int)(array_shift($par));
        $this->db->update('users', ['status' => 'banned'], ['id' => $par]);
        header("Location: /admin");
    }

    public function unbanAction()
    {
        $par = $this->getRequest()->getParams();
        $par = (int)(array_shift($par));
        $this->db->update('users', ['status' => 'confirmed'], ['id' => $par]);
        header("Location: /admin");
    }

    public function searchAction()
    {
        if (isset($_POST['search']) && iconv_strlen($_POST['search']) > 1) {
            $search = $_POST['search'];
            $search = preg_replace("#[^0-9a-z]#i", "", $search);
            $finder = $this->db->query("SELECT users.login, categories.name, ads.title, ads.text, ads.phone, status
              FROM users LEFT JOIN ads ON(ads.user_id=users.id) LEFT JOIN categories ON(categories.id=ads.category_id)
              WHERE login LIKE '%$search%' OR categories.name LIKE '%$search%' OR title LIKE '%$search%'", array('login' => $search));
            if (empty($finder)) {
                echo '<p><strong>попробуйте другое ключевое слово</strong></p><br />';
            } else {
                foreach ($finder as $tt) {
                    echo
                        '<tr><td>' . $tt["login"] . '</td>
                <td>' . $tt["phone"] . '</td>
                <td>' . $tt["status"] . '</td>
                <td>' . $tt["name"] . '</td>
                <td>' . $tt["title"] . '</td></tr>';
                }
            }
        }
    }

    public function categoriesAction()
    {
        $categories = $this->db->query("SELECT * FROM categories WHERE pid=0", []);
        $this->render('admin/categories', ['items' => $this->getCategory(0, 0), 'menu' => $this->menu(), 'categories' => $categories]);
    }

    private function getCategory($cats, $parent_id, $only_parent = false)
    {
        $test = $this->db->query("SELECT id, categories.name, pid FROM categories", []);
        $cats = array();
        foreach ($test as $cat) {
            $cats_ID[$cat['id']][] = $cat;
            $cats[$cat['pid']][$cat['id']] = $cat;
        }

        if (is_array($cats) && isset($cats[$parent_id])) {
            $tree = '<ul><h3>';
            if ($only_parent == false) {
                foreach ($cats[$parent_id] as $cat) {
                    $tree .= '<li><a href="/categories/property/' . $cat['id'] . '">' . $cat['name'] . '</a>';
                    $tree .= $this->getCategory($cats, $cat['id']);
                    $tree .= '</li>';
                }
            } elseif (is_numeric($only_parent)) {
                $cat = $cats[$parent_id][$only_parent];
                $tree .= '<li>' . $cat['name'] . ' #' . $cat['id'];
                $tree .= $this->getCategory($cats, $cat['id']);
                $tree .= '</li>';
            }
            $tree .= '</h3></ul>';
        } else return null;
        return $tree;
    }

    private function menu()
    {
        $array = $this->db->query("SELECT categories.name, categories.id FROM ads
                            LEFT JOIN categories ON(ads.category_id=categories.id)", []);
        $array = array_map('unserialize', array_unique(array_map('serialize', $array)));
        return $array;
    }

    public function personal_pageAction()
    {
        $id = $this->getRequest()->getParams();
        $id = (int)(array_shift($id));
        $plan = $this->get_plan($id);
        foreach ($plan as $p) {
            $this->render('admin/personal_page', ['row' => $this->get_user($id), 'plan' => $p, 'menu' => $this->menu(), 'comment' => $this->get_comments($id)]);
        }
    }

    private function get_user($id)
    {
        return $this->db->fetchRow('users', ['name', 'login', 'phone', 'email', 'date_create'], ['id' => $id]);
    }

    private function get_plan($id)
    {
        return $this->db->query("SELECT plans.name, price, count_ads, payments.start_date, payments.end_date
            FROM payments LEFT JOIN plans ON (payments.plan_id=plans.id) WHERE payments.user_id='$id'", []);
    }

    private function get_comments($id)
    {
        return $this->db->query("SELECT comments.text, comments.date_create, ads.title, ads.id
            FROM comments LEFT JOIN ads ON (comments.ad_id=ads.id) WHERE comments.user_id='$id'", []);
    }

    public function addCatAction()
    {
        if (iconv_strlen($_POST['name']) > 1 && iconv_strlen($_POST['description']) > 1) {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $pid = (int)$_POST['pid'];
            if (isset($_POST['checkbox']) && $_POST['checkbox'] == 1) {
                $this->db->insert('categories', ['name' => $name, 'description' => $description, 'pid' => $pid]);
                header("Location: /categories");
            } else
                $this->db->insert('categories', ['name' => $name, 'description' => $description]);
            header("Location: /categories");
        }
    }

    public function showAction()
    {
        $par = $this->getRequest()->getParams();
        $par = (int)(array_shift($par));
        if ($par == 999) {
            $row = $this->db->query("SELECT title, text, ads.date_create, categories.name,
            users.login, users.id FROM ads LEFT JOIN categories ON(ads.category_id=categories.id)
            LEFT JOIN users ON(ads.user_id=users.id)", []);
            $this->render('admin/show', ['menu' => $this->menu(), 'row' => $row]);
        }
        $row = $this->db->query("SELECT title, text, ads.date_create, categories.name,
            users.login, users.id FROM ads LEFT JOIN categories ON(ads.category_id=categories.id)
            LEFT JOIN users ON(ads.user_id=users.id) WHERE ads.category_id='$par'", []);
        $this->render('admin/show', ['menu' => $this->menu(), 'row' => $row]);
    }

    public function cat_propertyAction()
    {
        $par = $this->getRequest()->getParams();
        $par = (int)(array_shift($par));
        $property_cat = $this->db->query("SELECT property_cats.category_id, property_cats.property_id, property_cats.id, properties.name,
        properties.type, properties.params FROM property_cats
        LEFT JOIN properties ON (property_cats.property_id=properties.id)
        LEFT JOIN categories ON (property_cats.category_id=categories.id) WHERE property_cats.category_id='$par'", []);
        $property_all = $this->db->fetchAll('properties', ['name', 'id', 'type', 'params']);
        $this->render('admin/category_properties', ['row' => $par, 'property_all' => $property_all, 'cat_id' => $par, 'property' => $property_cat]);
    }

    public function add_propertyAction()
    {
        if (isset($_POST['per_cat']) && !(empty($_POST['add']))) {
            $arr = $_POST['add'];
            while (list($key, $value) = each($arr)) {
                $this->db->insert('property_cats', ['category_id' => intval($value), 'property_id' => $key]);
            }
            $id = (int)array_shift($arr);
            header("Location: /categories/property/{$id}");
        }
        if (isset($_POST['add_cat']) && iconv_strlen($_POST['property_name']) > 1 && iconv_strlen($_POST['property_type']) > 1) {
            $id = array_keys($_POST['id']);
            $id = array_shift($id);
            $property_name = $_POST['property_name'];
            $property_values = json_encode($_POST['property_values']);
            $property_type = $_POST['property_type'];
            $this->db->insert('properties', ['name' => $property_name, 'params' => $property_values, 'type' => $property_type]);
            header("Location: /categories/property/{$id}");
        }
        if (isset($_POST['delete'])) {
            $id = intval($_POST['delete']);
            $this->db->delete('property_cats', ['id' => $id]);
        } else echo "<h1>Проверте правильность запроса и повторите попытку</h1>";
    }
}