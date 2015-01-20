<?php

use application\classes\Registry as Registry;
use application\core\Model;

class Category extends Model
{

    const TABLE = 'categories';

    public function __construct()
    {
        parent::__construct();
    }

    public function create()
    {
        $table = Category::TABLE;
        $data = array();
        $errorLog = array();

        if (empty(trim($_POST['name']))) {
            $errorLog['name'] = 'Enter category name';
        } else {
            if (strlen($_POST['name']) < 5) {
                $errorLog['name'] = 'Too short category name';
            } elseif (strlen($_POST['name']) > 15) {
                $errorLog['name'] = 'Too long category name';
            } else {
                $data['name'] = $_POST['name'];
            }
        }

        if (empty(trim($_POST['description']))) {
            $errorLog['description'] = 'Enter category description';
        } else {
            if (strlen($_POST['description']) < 15) {
                $errorLog['name'] = 'Too short category description';
            } elseif (strlen($_POST['description']) > 15) {
                $errorLog['description'] = 'Too long category description';
            } else {
                $data['description'] = $_POST['description'];
            }
        }

        if (empty($errorLog)) {
            $this->db->insert($table, $data);
        } else {
            return $errorLog;
        }
    }

    public function edit($category_id)
    {
        $table = Category::TABLE;
        $data = array();
        $errorLog = array();

        if (empty(trim($_POST['name']))) {
            $errorLog['name'] = 'Enter category name';
        } else {
            if (strlen($_POST['name']) < 5) {
                $errorLog['name'] = 'Too short category name';
            } elseif (strlen($_POST['name']) > 15) {
                $errorLog['name'] = 'Too long category name';
            } else {
                $data['name'] = $_POST['name'];
            }
        }

        if (empty(trim($_POST['description']))) {
            $errorLog['description'] = 'Enter category description';
        } else {
            if (strlen($_POST['description']) < 15) {
                $errorLog['description'] = 'Too short category description';
            } elseif (strlen($_POST['description']) > 15) {
                $errorLog['description'] = 'Too long category description';
            } else {
                $data['description'] = $_POST['description'];
            }
        }

        if (empty($this->getCategoryById($category_id))) {
            $errorLog['category_id'] = "Category don't exist";
        }

        if (empty($errorLog)) {
            $this->db->update($table, $data . ['category_id' => $category_id]);
        } else {
            return $errorLog;
        }
    }


    public function delete($category_id)
    {
        $table = Category::TABLE;

        $this->db->delete($table, ['category_id' => $category_id]);
    }

    public function getCategoryById($id)
    {
        $table = Category::TABLE;

        return $this->db->fetchRow($table, ['*'], ['category_id' => $id]);
    }

    public function getCategoryByName($name)
    {
        $table = Category::TABLE;

        return $this->db->fetchRow($table, ['*'], ['name' => $name]);
    }

    public function getAllCategoriesName()
    {
        $table = Category::TABLE;
        $cat = $this->db->fetchAll($table, ['name']);
        $arr = [];

        foreach ($cat as $val) {
            $arr[] = $val['name'];
        }

        return $arr;
    }

    public function getAllCategories()
    {
        $table = Category::TABLE;

        return $this->db->fetchAll($table, ['*']);
    }

    public function getCategoryHierarchy(){
        $table = Category::TABLE;

        /*$data = array();
        $index = array();
        $query = $this->db->query("SELECT id, pid, name FROM categories ORDER BY name");
        foreach($query as $row) {
            $id = $row["id"];
            $parent_id = $row["pid"] == '0' ? "NULL" : $row["pid"];
            $data[$id] = $row;
            $index[$parent_id][] = $id;
        }

        $hierarchy=array();

        $buildHierarchy=function($parent_id, $level) use ($data,$index,$hierarchy,&$buildHierarchy){
            $parent_id = $parent_id == '0' ? "NULL" : $parent_id;
            if (isset($index[$parent_id])) {
                foreach ($index[$parent_id] as $id) {
                    echo str_repeat("-", $level) . $data[$id]["name"] . "\n";
                    $buildHierarchy($id, $level + 1);
                }
            }
        };

        $buildHierarchy('0',0);*/

        $refs = array();
        $list = array();

        $sql = "SELECT id, pid, name FROM categories ORDER BY name";

        $result = $this->db->query($sql);

        foreach ($result as $row)
        {
            $ref = & $refs[$row['id']];

            $ref['pid'] = $row['pid'];
            $ref['name']      = $row['name'];

            if ($row['pid'] == 0)
            {
                $list[$row['id']] = & $ref;
            }
            else
            {
                $refs[$row['pid']]['children'][$row['id']] = & $ref;
            }
        }

        echo $this->toUL($list);

    }

    function toUL(array $array)
    {
        $html = '<ul>' . PHP_EOL;

        foreach ($array as $value)
        {
            $html .= '<li>' . $value['name'];
            if (!empty($value['children']))
            {
                $html .= $this->toUL($value['children']);
            }
            $html .= '</li>' . PHP_EOL;
        }

        $html .= '</ul>' . PHP_EOL;

        return $html;
    }

} 