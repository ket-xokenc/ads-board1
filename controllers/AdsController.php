<?php
use application\core\BaseController as BaseController;


class AdsController extends BaseController{

    public function createAction(){
        $users=new Users();
        $category=new Category();
        $ads=new Ads($category,$users);
        $errors=array();
        $userInfo = $users->get();
        if(empty($users->getUid())){
            header("Location: http://{$_SERVER['HTTP_HOST']}/");
            return;
        }

        if($this->getRequest()->isPost()){
            if(!empty($errors=$ads->create())){
               $this->render('users/newAds',['dbinfo'=>$category->getAllCategories(), 'user' => $userInfo],$errors);
            }else{
                header("Location: http://{$_SERVER['HTTP_HOST']}/profile");
                $this->render('users/profile',['dbinfo'=>$ads->getAdsByUserId($users->getUid()),'info'=>'Ads successfuly created','user' => $userInfo]);
            }
        }else{
            $this->render('users/newAds',['dbinfo'=>$category->getAllCategories(), 'user' => $userInfo]);
        }
    }

    public function editAction(){
        $users=new Users();
        $userInfo = $users->get();
        $category=new Category();
        $ads=new Ads($category,$users);
        $params=$this->getRequest()->getParams();
        $errors=array();

        if(empty($users->getUid())){
            header("Location: http://{$_SERVER['HTTP_HOST']}/");
            return;
        }

        if($this->getRequest()->isPost()){
            if(!empty($errors=$ads->edit($params[0]))){
                if(!empty($errors['redirect'])){
                    header("Location: http://{$_SERVER['HTTP_HOST']}{$errors['redirect']}");
                }
                $this->render('users/edit-ads',['dbinfo'=>$ads->getAdsById($params[0]), 'user' => $userInfo],$errors);
            }else{
                header("Location: http://{$_SERVER['HTTP_HOST']}/profile");
                $this->render('users/profile',['dbinfo'=>$ads->getAdsByUserId($users->getUid()),'info'=>'Ads successfuly edited', 'user' => $userInfo]);
            }
        }else{
            $this->render('users/edit-ads',['dbinfo'=>$ads->getAdsById($params[0]), 'user' => $userInfo]);
        }
    }

    public function deleteAction(){
        $users=new Users();
        $userInfo = $users->get();
        $category=new Category();
        $ads=new Ads($category,$users);
        $params=$this->getRequest()->getParams();
        $errors=array();


        if(empty($users->getUid())){
            header("Location: http://{$_SERVER['HTTP_HOST']}/");
            return;
        }

        if(!empty($errors=$ads->delete($params[0]))){
            if(!empty($errors['redirect'])){
                header("Location: http://{$_SERVER['HTTP_HOST']}{$errors['redirect']}");
                return;
            }
        }


        header("Location: http://{$_SERVER['HTTP_HOST']}/profile");
        $this->render('users/profile',['dbinfo'=>$ads->getAdsByUserId($users->getUid()),'info'=>'Ads successfuly deleted', 'user' => $userInfo]);

    }

} 