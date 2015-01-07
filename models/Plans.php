<?php
use application\core\Model;

class Plans extends Model
{
    protected $table = 'plans';

    public function __construct()
    {
        parent::__construct();
    }

    public function getActivePlans()
    {
        return $this->db->fetchAll($this->table, ['*']);
    }

    public function getPriceByName($name)
    {
        return $this->db->fetchOne($this->table, 'price', ['name' => $name]);
    }
}