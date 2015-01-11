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

    public function getPlanIdByName($name)
    {
        return $this->db->fetchOne($this->table, 'id', ['name' => $name]);
    }

    public function switchPlan()
    {
        if (isset($_SESSION['user_id'])) {
            $activePayment = $this->db->query("
                select * from plans inner join users on users.plan_id = plans.id where users.id = {$_SESSION['user_id']}
            ");
            if (!$activePayment) {
                $this->db->update('users', ['plan_id' => 1], ['id' => $_SESSION['user_id']]);
            }
        }
    }
}