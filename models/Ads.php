<?php

use application\classes\Registry;
use application\core\Model;
use application\classes\Session;
use Sphinx\SphinxClient;

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
        $data = array();
        $errorLog = array();


        if (($errorLog = $this->validator->validate($_POST)) !== true) {
            return $errorLog;
        }

        $data['category_id'] = $this->category->getCategoryByName($_POST['category'])['id'];
        $data['user_id'] = Session::get('user_id');
        $data['date_create'] = date('Y-m-d H:i:s');
        $data['title'] = $_POST['title'];
        $data['text'] = $_POST['text'];

        $this->db->insert($table, $data);

        $path='../public/tmp_files/'.$data['user_id'];

        if(is_dir($path)){
            if(!is_dir('../public/files/')){
                mkdir('../public/files/');
            }
                rename($path, '../public/files/'.$this->db->getLastInsertedId());
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
                    select count(*) from ads, users where ads.user_id = :userId and ads.date_create > :startDate
                ", [':userId' => $userId, ':startDate' => $startDate]);

                $currentCnt = current($currentCnt);
                $currentCnt = array_pop($currentCnt);

                $tableCnt = $lastPayment['count_ads'];

                if(($currentCnt <= $tableCnt || $tableCnt == -1) && $user['plan_id'] != 1) {
                    return true;
                } else {
                    return 'Limit is exceeded';
                }


            } else {
                return 'You should buy payment plan!';
            }
        } else {
            $cntAds = $this->db->query("
                        select count(*) from ads where ads.user_id = :userId
            ", [':userId' => $userId]);
            $cntAds = current($cntAds);
            $cntAds = array_pop($cntAds);
            $cntAdsTable = $plansInfo['count_ads'];
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
       // $cl->SetMatchMode(SPH_MATCH_ANY);
        $result = $cl->Query($string);
        if ( $result !== false ) {
             if (!empty($result["matches"])){
                 $found = array_keys($result['matches']); //id found ads
                 $res = array();
                for ($j = 0; $j < count($found); $j++) {
                    $temp = $this->db->query(" SELECT * FROM $table WHERE ads_id = $found[$j]");
                    $res = array_merge($res, $temp);
                }
                 return $res;
            }
        }
        else  return '';
    }
}
