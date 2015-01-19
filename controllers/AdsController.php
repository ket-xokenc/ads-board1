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
            $errors = $ads->checkAddAds();
            if ($errors !== true) {
                $this->render('users/newAds', ['error' => [$errors], 'dbinfo' => $category->getAllCategories(), 'user' => $users->get()]);
                exit;
            }
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
        $this->addView('coments', 'coments/show');
        $this->render('site/show-ads', ['dataComments'=> $dataComments,'dbinfo' => $dbinfo,'imgs'=>$imgs,'thumbnails'=>$thumbnails, 'user' => $users->get()]);
    }
} 