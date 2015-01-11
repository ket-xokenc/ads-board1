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
            foreach(scandir($path) as $img){
                rename($path, '../public/files/'.$this->db->getLastInsertedId());
            }
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
        $currentDate = date('Y-m-d H:i:s');
        $plans = new Plans();
        $plansInfo = current($plans->getActivePlans());

        $lastPayment = $this->db->query("
                    Select * from payments WHERE payments.user_id = :userId ORDER BY payments.end_date DESC LIMIT 1
        ", [':userId' => $userId]);

        $lastPayment = current($lastPayment);
        if($lastPayment) {
            $activePayment = $this->db->query("
             select * from payments where payments.end_date > CURDATE() AND payments.id = {$lastPayment['id']}
            ");

            $activePayment = current($activePayment);

            if($activePayment) {
                $currentCnt = $this->db->query("
                    select count(*) from ads inner join users on users.id = ads.user_id INNER JOIN payments on payments.user_id = users.id
                            where payments.id = {$activePayment['id']}
                            AND payments.start_date <= ads.date_create
                            AND payments.end_date >= ads.date_create
                ");
                $currentCnt = current($currentCnt);
                $currentCnt = array_pop($currentCnt);
                $tableCnt = $this->db->query("
                    select count_ads from plans inner join users on plans.id = users.plan_id where users.id = $userId
                ");
                $tableCnt = current($tableCnt);
                $tableCnt = array_pop($tableCnt);

                if($currentCnt <= $tableCnt || $tableCnt == -1) {
                    return true;
                } else {
                    return 'Limit is exceeded';
                }


            } else {
                return 'You should buy payment plan!';
            }
        } else {
            $cntAds = $this->db->query("
                        select count(*) from ads where ads.user_id = $userId
            ");
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
        $table=Ads::TABLE;

        return $this->db->fetchRow($table, ['*'], ['id' => $id]);
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

    public function getAdsByUserName($name)
    {
        $table = Ads::TABLE;

        return $this->db->query("Select * from users inner join $table on
                                    $table.user_id=users.id where users.name=:name", array(':name' => $name));
    }

    public function getAdsByTitle($title, $is_regex = false)
    {
        $table = Ads::TABLE;

        if (!$is_regex) {
            $title = '^' . $title . '$';
        }

        return $this->db->query("SELECT * FROM $table WHERE title REGEXP :title", array(':title' => $title));
    }

    public function getAdsByText($text, $is_regex = false)
    {
        $table = Ads::TABLE;

        if (!$is_regex) {
            $text = '^' . $text . '$';
        }

        return $this->db->query("SELECT * FROM $table WHERE text REGEXP :text", array(':text' => $text));
    }

    public function getAllAds()
    {
        $table = Ads::TABLE;

        return $this->db->query("Select users.name as user_name,
              users.phone as users_phone,
              ads.date_create as ads_date_create,
              categories.name as categories_name,
              ads.title as ads_title,
              ads.text as ads_text
             from users inner join $table on
                                    $table.user_id=users.id
                        inner join categories ON
                                    categories.id = ads.category_id
                        ORDER BY $table.date_create DESC");
    }

    public function getAdsByDate($date, $is_regex = false)
    {
        $table = Ads::TABLE;

        if (!$is_regex) {
            $date = '^' . $date . '$';
        }

        return $this->db->query("SELECT * FROM $table WHERE date_create REGEXP :date",array(':date'=>$date));
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

} 