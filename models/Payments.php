<?php
/**
 * Created by PhpStorm.
 * User: alexandr
 * Date: 08.01.15
 * Time: 11:41
 */
use application\core\Model;
use application\classes\Session;

class Payments extends Model
{
    protected $table = 'payments';
    public function __construct()
    {
        parent::__construct();
    }

    public function saveTransaction($transactionId, $planName)
    {
        $userId = Session::get('user_id');
        $users = new Plans();
        $planId = $users->getPlanIdByName($planName);
        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime("+30 days"));
        $this->db->insert($this->table, ['user_id' => $userId, 'plan_id' => $planId, 'transaction_id' => $transactionId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

    }
}