<?php
use application\core\BaseController as BaseController;


class AdsController extends BaseController
{

    public function createAction()
    {
        $users = new Users();
        $category = new Category();
        $ads = new Ads($category);
        $errors = array();

        if ($this->getRequest()->isPost()) {
            $errors = $ads->checkAddAds();
            if($errors !== true) {
                $this->render('users/newAds', ['error' => [$errors],'dbinfo' => $category->getAllCategories(), 'user' => $users->get()]);
                exit;
            }
            if (!empty($errors = $ads->create())) {
                $this->render('users/newAds', ['dbinfo' => $category->getAllCategories(), 'user' => $users->get()], $errors);
            } else {
                header("Location: http://{$_SERVER['HTTP_HOST']}/profile");
                $this->render('users/profile', ['dbinfo' => $ads->getAdsByUserId($users->getUid()), 'user' => $users->get(), 'info' => 'Ads successfuly created']);
            }
        } else {
            $this->render('users/newAds', ['dbinfo' => $category->getAllCategories(), 'user' => $users->get()]);
        }
    }

    public function editAction()
    {
        $users = new Users();
        $category = new Category();
        $ads = new Ads($category, $users);
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
                $this->render('users/profile', ['dbinfo' => $ads->getAdsByUserId($users->getUid()), 'user' => $users->get(), 'info' => 'Ads successfuly edited']);
            }
        } else {
            $this->render('users/edit-ads', ['dbinfo' => $ads->getAdsById($params[0]), 'user' => $users->get()]);

        }
    }

    public function deleteAction()
    {
        $users = new Users();
        $category = new Category();
        $ads = new Ads($category, $users);
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

        $this->render('users/profile', ['dbinfo' => $ads->getAdsByUserId($users->getUid()), 'user' => $users->get(), 'info' => 'Ads successfuly deleted']);


    }


} 