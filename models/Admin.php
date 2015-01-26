<?php
/**
 * Created by PhpStorm.
 * User: ed
 * Date: 25.01.15
 * Time: 11:15
 */
use application\core\Model;
use application\classes\Registry;

class Admin extends Model
{
    protected $db;

    function __construct()
    {
        $this->db = Registry::get('database');
        $this->user = new Users();
    }

    public function register()
    {
        $this->user->authorize();
        Users::isAuthorized();
    }

    public function isAdmin()
    {
        $array = $this->user->get();
        if ($array['role'] == 'admin')
            return true;
        else return false;
    }

    public function cat_property($par)
    {
        return $this->db->query("SELECT property_cats.category_id, property_cats.property_id, property_cats.id, properties.name,
            properties.type, properties.params FROM property_cats
            LEFT JOIN properties ON (property_cats.property_id=properties.id)
            LEFT JOIN categories ON (property_cats.category_id=categories.id)
            WHERE property_cats.category_id='$par'", []);
    }

    public function addPropertyToCat($arr)
    {
        while (list($key, $value) = each($arr)) {
            $this->db->insert('property_cats', ['category_id' => intval($value), 'property_id' => $key]);
        }
    }

    public function properties()
    {
        return $this->db->fetchAll('properties', ['name', 'id', 'type', 'params']);
    }

    public function addProperty($property_name, $property_type, $property_values)
    {
        $this->db->insert('properties', ['name' => $property_name, 'params' => $property_values, 'type' => $property_type]);
    }

    public function menu()
    {
        $array = $this->db->query("SELECT categories.name, categories.id FROM ads LEFT JOIN categories ON(ads.category_id=categories.id)", []);
        $array = array_map('unserialize', array_unique(array_map('serialize', $array)));
        return $array;
    }

    public function getCategoriesPid()
    {
        return $this->db->fetchAll('categories', ['*'], ['pid' => 0]);
    }

    public function addCategory($name, $description, $pid = 0)
    {
        $this->db->insert('categories', ['name' => $name, 'description' => $description, 'pid' => $pid]);
    }

    public function panel()
    {
        return $this->db->query("SELECT users.id, login, status, users.phone, users.date_create, COUNT(ads.user_id) AS  caunt
                                FROM users LEFT JOIN ads ON(users.id=ads.user_id) GROUP BY users.id", []);
    }

    public function get_user($id)
    {
        return $this->db->fetchRow('users', ['name', 'login', 'phone', 'email', 'date_create'], ['id' => $id]);
    }

    public function get_plan($id)
    {
        return $this->db->query("SELECT plans.name, price, count_ads, payments.start_date, payments.end_date
            FROM payments LEFT JOIN plans ON (payments.plan_id=plans.id) WHERE payments.user_id='$id'", []);
    }

    public function deletePropertyCat($id)
    {
        return $this->db->delete('property_cats', ['id' => $id]);
    }

    public function get_comments($id)
    {
        return $this->db->query("SELECT comments.text, comments.date_create, ads.title, ads.id
            FROM comments LEFT JOIN ads ON (comments.ad_id=ads.id) WHERE comments.user_id='$id'", []);
    }

    public function show($row = 0)
    {
        $select = "SELECT title, text, ads.date_create, categories.name,
            users.login, users.id FROM ads LEFT JOIN categories ON(ads.category_id=categories.id)
            LEFT JOIN users ON(ads.user_id=users.id)";
        if (empty($row)) {
            return $this->db->query($select, []);
        } else return $this->db->query("{$select} WHERE ads.category_id='$row'", []);
    }

    public function banUnban($act, $par)
    {
        return $this->db->update('users', ['status' => $act], ['id' => $par]);
    }

    public function search($search)
    {
        return $this->db->query("SELECT users.login, categories.name, ads.title, ads.text, ads.phone, status
              FROM users LEFT JOIN ads ON(ads.user_id=users.id) LEFT JOIN categories ON(categories.id=ads.category_id)
              WHERE login LIKE '%$search%' OR categories.name LIKE '%$search%' OR title LIKE '%$search%'", array('login' => $search));
    }

    public function treeCategory($cats = 0, $parent_id = 0)
    {
        $test = $this->db->fetchAll('categories', ['*'], []);
        $cats = array();
        foreach ($test as $cat) {
            $cats[$cat['pid']][$cat['id']] = $cat;
        }

        if (is_array($cats) && isset($cats[$parent_id])) {
            $tree = '<ul><h3>';
            foreach ($cats[$parent_id] as $cat) {
                $tree .= '<li><a href="/categories/property/' . $cat['id'] . '">' . $cat['name'] . '</a>';
                $tree .= $this->treeCategory($cats, $cat['id']);
                $tree .= '</li>';
            }
            $tree .= '</h3></ul>';
        } else return null;
        return $tree;
    }
}