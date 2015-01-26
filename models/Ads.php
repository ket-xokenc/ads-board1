<?php

use application\classes\Registry;
use application\core\Model;
use application\classes\Session;
class Ads extends Model
{


    const TABLE = 'ads';
    private $category;

    public function __construct(Category $category)
    {
        parent::__construct();
        $this->category = $category;
        $this->validator->setRules($this->validRules());
        $this->validator->setMessages($this->validMessages());
    }

    private function validRules()
    {
        return ['title' => [
            'type' => 'text',
            'maxlength' => '250',
            'minlength' => '10'
        ],
            'text' => [
                'type' => 'text',
                'maxlength' => '1024',
                'minlength' => '20'
            ],
            'category' => [
                'type' => 'select',
                'match_collection' => $this->category->getAllCategoriesName()
            ]
        ];
    }

    private function validMessages()
    {
        return ['title' => [
            'maxlength' => 'Too long title',
            'minlength' => 'Too short title'
        ],
            'text' => [
                'maxlength' => 'Too long description',
                'minlength' => 'Too short description',
            ],
            'category' => [
                'match_collection' => 'Invalid category',
                'validation' => 'Invalid category'
            ]
        ];
    }

    public function create()
    {
        $table = Ads::TABLE;
        $data1 = array();
        $data2 = array();
        $errorLog = array();


        /*if (($errorLog = $this->validator->validate($_POST)) !== true) {
            return $errorLog;
        }*/

        $data1['category_id'] = $this->category->getCategoryByName($_POST['subcategory'])['id'];
        $data1['user_id'] = Session::get('user_id');
        $data1['date_create'] = date('Y-m-d H:i:s');
        $data1['title']=$_POST['title'];
        $data1['text']=$_POST['text'];

        $this->db->insert($table, $data1);

        $lastInsertId=$this->db->getLastInsertedId();

        foreach($_POST as $key=>$value){
            if(strrpos($key,'ads_field_')===0) {
                $data2['property_id'] = preg_split('#_#', $key)[2];
                $data2['ads_id'] = $lastInsertId;

                if (!is_array($value)) {
                    $data2['value'] = $value;
                } else {
                    $data2['value'] = json_encode($value);
                }

                $this->db->insert('property_ads', $data2);
            }
        }


        $path='../public/tmp_files/'.$data1['user_id'];

        if(is_dir($path)){
            if(!is_dir('../public/files/')){
                mkdir('../public/files/');
            }
                rename($path, '../public/files/'.$lastInsertId);
        }
    }

    public function edit($ads_id)
    {
        $table = Ads::TABLE;
        $data = array();
        $errorLog = array();

        if (($errorLog = $this->validator->validate($_POST)) !== true) {
            return $errorLog;
        }

        $data['user_id'] = Session::get('user_id');
        $data['date_create'] = date('Y-m-d H:i:s');
        $data['title'] = $_POST['title'];
        $data['text'] = $_POST['text'];


        $res = $this->db->query("select ads.id from ads inner join users on users.id=ads.user_id where ads.id=$ads_id and users.id={$data['user_id']}");

        if (empty($res)) {
            $errorLog['redirect'] = "/";
            return $errorLog;
        }


        $this->db->update($table,$data,['id'=>$ads_id]);


    }


    public function delete($ads_id)
    {
        $table = Ads::TABLE;
        $errorLog = array();

        $user_id = Session::get('user_id');

        $res = $this->db->query("select ads.id from ads inner join users on users.id=ads.user_id where ads.id=$ads_id and users.id=$user_id");

        if (empty($res)) {
            $errorLog['redirect'] = "/";
            return $errorLog;
        }

        $this->db->delete($table, ['id' => $ads_id]);
    }

    public function checkAddAds()
    {
        $userId = Session::get('user_id');
        $user = Session::get('user');
        $currentDate = date('Y-m-d H:i:s');
        $plans = new Plans();
        $plansInfo = current($plans->getActivePlans());
        $userPlanId = $this->db->fetchOne('users', 'plan_id', ['id' => $userId]);

        $lastPayment = $this->db->query("
                    Select payments.*, plans.count_ads from payments, plans WHERE payments.user_id = :userId AND plans.id = payments.plan_id ORDER BY payments.end_date DESC LIMIT 1
        ", [':userId' => $userId]);

        if ($lastPayment) {
            $lastPayment = current($lastPayment);
            $endDate = $lastPayment['end_date'];
            $startDate = $lastPayment['start_date'];

            $activePayment  =false;
            if ($endDate > $currentDate) {
                $activePayment = true;
            }

            if($activePayment) {
                $currentCnt = $this->db->query("
                    select count(*) from ads, users where ads.user_id = :userId and ads.date_create > :startDate group by users.id
                ", [':userId' => $userId, ':startDate' => $startDate]);

                if ($currentCnt) {
                    $currentCnt = current($currentCnt);
                    $currentCnt = array_pop($currentCnt);
                } else {
                    $currentCnt = 0;
                }

                $tableCnt = $lastPayment['count_ads'];
                if ($tableCnt == -1) {
                    Session::set('countAds', -1);
                }
                if(($currentCnt <= $tableCnt || $tableCnt == -1) && $userPlanId != 1) {
                    Session::set('countAds', $tableCnt - $currentCnt);
                    return true;
                } else {
                    Session::set('countAds', $currentCnt);
                    return 'Limit is exceeded';
                }


            } else {
                Session::set('countAds', 0);
                return 'You should buy payment plan!';
            }
        } else {
            $cntAds = $this->db->query("
                        select count(*) from ads where ads.user_id = :userId
            ", [':userId' => $userId]);
            $cntAds = current($cntAds);
            $cntAds = array_pop($cntAds);
            $cntAdsTable = $plansInfo['count_ads'];
            Session::set('countAds', $cntAdsTable - $cntAds);
            if($cntAds >= $cntAdsTable) {
                return 'Limit is exceeded';
            } else {
                return true;
            }
        }
    }

    public function getAdsByString($string)
    {
        $table=Ads::TABLE;
        return $this->db->query("
                              Select users.name user_name, users.phone users_phone, categories.name categories_name,
                                    $table.title {$table}_title, $table.text {$table}_text, $table.date_create {$table}_date_create,
                                    $table.id {$table}_id
                                    from $table inner join categories on
                                    $table.category_id=categories.id
                                    inner join users on users.id=$table.user_id
                                    where $table.title like '%".$string."%' order by $table.date_create DESC limit 5");
    }

    public function getAdsById($id){
        $table = Ads::TABLE;

        return $this->db->query("Select users.name as user_name,
              users.phone as users_phone,
              ads.date_create as ads_date_create,
              categories.name as categories_name,
              ads.title as title,
              ads.text as text,
              ads.id as ads_id
             from users inner join $table on
                                    $table.user_id=users.id
                        inner join categories ON
                                    categories.id = ads.category_id
                                    WHERE ads.id=$id");
    }

    public function getAdsByUserId($id, $escape = 0, $number = PHP_INT_MAX)
    {
        $table = Ads::TABLE;


        return $this->db->query("Select users.name user_name,users.phone users_phone,categories.name categories_name,
                                    $table.title {$table}_title, $table.text {$table}_text, $table.date_create {$table}_date_create,
                                    $table.id {$table}_id
                                    from $table inner join categories on
                                    $table.category_id=categories.id
                                    inner join users on users.id=$table.user_id
                                    where users.id=:id  order by $table.date_create DESC limit $escape,$number ", array(':id' => $id));
    }

    public function getAdsByCategoryId($id)
    {
        $table = Ads::TABLE;

        return $this->db->fetchAll($table, ['*'], ['category_id' => $id]);
    }

    public function getAdsByCategoryName($name)
    {
        $table = Ads::TABLE;

        return $this->db->query("Select * from $table inner join categories on
                                    $table.category_id=categories.id where categories.name=:name", array(':name' => $name));
    }

    public function getAllAds()
    {
        $table = Ads::TABLE;

        return $this->db->query("Select users.name as user_name,
              users.phone as users_phone,
              ads.date_create as ads_date_create,
              categories.name as categories_name,
              ads.title as ads_title,
              ads.text as ads_text,
              ads.id as ads_id
             from users inner join $table on
                                    $table.user_id=users.id
                        inner join categories ON
                                    categories.id = ads.category_id
                        ORDER BY $table.date_create DESC");
    }

    public function getNumberOfAds($user_id)
    {
        $table = Ads::TABLE;

        if (empty($user_id)) {
            return $this->db->query("SELECT count(*) FROM $table")[0][0];
        } else {
            return $this->db->query("SELECT count(*) FROM $table where $table.user_id=$user_id")[0][0];
        }
    }
    public function getAdsByText($string)
    {
        $table = 'search';
        $cl = new SphinxClient ();
        $cl->SetServer("localhost", 3312);
        $cl->SetConnectTimeout(1);
        $cl->SetRankingMode(SPH_RANK_PROXIMITY_BM25);
      //$cl->SetMatchMode(SPH_MATCH_ANY);
        $result = $cl->Query($string);
        if ( $result !== false ) {
             if (!empty($result["matches"])) {
                 $found = array_keys($result['matches']); //id found ads
                 $res = array();
                 if (!empty($_POST['page'])) {
                     $userId = Session::get('user_id');
                     for ($j = 0; $j < count($found); $j++) {
                         $temp = $this->db->query(" SELECT * FROM $table WHERE ads_id = $found[$j] AND user_id = $userId");
                         if ($temp) {
                             $res = array_merge($res, $temp);
                         }
                     }
                 } else {
                     for ($j = 0; $j < count($found); $j++) {
                         $temp = $this->db->query(" SELECT * FROM $table WHERE ads_id = $found[$j]");
                         if ($temp) {
                             $res = array_merge($res, $temp);
                         }
                     }
                 }
                 return $res;
            }
        }
        else  return '';
    }
}
