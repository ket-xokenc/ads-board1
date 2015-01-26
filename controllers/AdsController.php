<?php
use application\core\BaseController as BaseController;

class AdsController extends BaseController
{

    public function createAction()
    {
        $users = new Users();
        $dataUser = $users->get();
        $category = new Category();
        $ads = new Ads($category);
        $errors = array();

        if ($this->getRequest()->isPost()) {
            /*$errors = $ads->checkAddAds();
            if ($errors !== true) {
                $this->render('users/newAds', ['error' => [$errors], 'dbinfo' => $category->getAllCategories(), 'user' => $users->get()]);
                exit;
            }*/
            if (!empty($errors = $ads->create())) {
                $this->render('users/newAds', ['dbinfo' => $category->getAllCategories(), 'user' => $users->get()], $errors);
            } else {
                header("Location: http://{$_SERVER['HTTP_HOST']}/profile");
                $this->render('users/profile', ['dbinfo' => $ads->getAdsByUserId($users->getUid()), 'user' => $users->get()]);
            }
        } else {
                $this->render('users/newAds', ['dbinfo' => $category->getAllCategories(), 'user' => $users->get()]);
        }
    }

    public function editAction()
    {
        $users = new Users();
        $category = new Category();
        $ads = new Ads($category);
        $params = $this->getRequest()->getParams();
        $errors = array();

        if ($this->getRequest()->isPost()) {
            if (!empty($errors = $ads->edit($params[0]))) {
                if (!empty($errors['redirect'])) {
                    header("Location: http://{$_SERVER['HTTP_HOST']}{$errors['redirect']}");
                    return;
                }
                $this->render('users/edit-ads', ['dbinfo' => $ads->getAdsById($params[0]), 'user' => $users->get()], $errors);
            } else {
                header("Location: http://{$_SERVER['HTTP_HOST']}/profile");
                $this->render('users/profile', ['dbinfo' => $ads->getAdsByUserId($users->getUid()), 'user' => $users->get()]);
            }
        } else {
            $this->render('users/edit-ads', ['dbinfo' => $ads->getAdsById($params[0]), 'user' => $users->get()]);

        }
    }

    public function deleteAction()
    {
        $users = new Users();
        $category = new Category();
        $ads = new Ads($category);
        $params = $this->getRequest()->getParams();
        $errors = array();


        if (empty($users->getUid())) {
            header("Location: http://{$_SERVER['HTTP_HOST']}/");
            return;
        }

        if (!empty($errors = $ads->delete($params[0]))) {
            if (!empty($errors['redirect'])) {
                header("Location: http://{$_SERVER['HTTP_HOST']}{$errors['redirect']}");
                return;
            }
        }


        header("Location: http://{$_SERVER['HTTP_HOST']}/profile");

        $this->render('users/profile', ['dbinfo' => $ads->getAdsByUserId($users->getUid()), 'user' => $users->get()]);


    }

    public function showAction()
    {
        $users = new Users();
        $category = new Category();
        $ads = new Ads($category);

        $params = $this->getRequest()->getParams();
        $dbinfo = $ads->getAdsById($params[0]);
        $thumbnails = array();
        $imgs = array();

        if (is_dir("../public/files/{$dbinfo[0]['ads_id']}/thumbnail") &&
            is_dir("../public/files/{$dbinfo[0]['ads_id']}")
        ) {
            $thumbnails = array_diff(scandir("../public/files/{$dbinfo[0]['ads_id']}/thumbnail"), array('..', '.'));
            $imgs = array_diff(scandir("../public/files/{$dbinfo[0]['ads_id']}"), array('..', '.', 'thumbnail'));
        }
        $comment = new Comment();

        $dataComments = $comment->getCommentsByAdId($dbinfo[0]['ads_id']);
        $treeComments = $this->mapTree($dataComments);
        $commentsString = self::commentsString($treeComments);


        $this->addView('coments', 'coments/show');
        $this->render('site/show-ads', ['comments'=> $commentsString,'dbinfo' => $dbinfo,'imgs'=>$imgs,'thumbnails'=>$thumbnails, 'user' => $users->get()]);
    }

    public function mapTree($dataset)
    {
        $tree = array();
        foreach ($dataset as $id=>&$node) {
            if (!$node['pid']) {
                $tree[$id] = &$node;
            } else {
                $dataset[$node['pid']]['childs'][$id] = &$node;
            }
        }
        return $tree;
    }

    public static function commentsString($data)
    {
        $string = '';
        foreach($data as $w) {
            $string .= self::commentsToTemplate($w);
        }
        return $string;
    }

    public static function commentsToTemplate($comment, $link = true)
    {
        ob_start();
        include '../views/coments/comment_template.phtml';

        $comments_string =  ob_get_clean(); // Получаем содержимое буфера в виде строки
        return $comments_string;
    }

    public function subCategoryAction(){
        if ($this->getRequest()->isPost()) {
            $category = new Category();
            echo json_encode($category->getSubCategories($_POST['category']));
        }
    }

    public function subCategoryFieldsAction(){
        $fields = new AdsFields();
        $this->layout='';
        $this->render('users/adsFields', ['dbinfo' => $fields->selectAllFields($_POST['subcategory'])]);
    }
} 