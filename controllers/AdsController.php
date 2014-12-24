<?php
use application\core\BaseController as BaseController;


class AdsController extends BaseController{

    public function createAction(){
        $users=new Users();
        $category=new Category();
        $ads=new Ads($category,$users);
        $errors=array();

        if(empty($users->getUid())){
            header("Location: http://{$_SERVER['HTTP_HOST']}/");
            return;
        }

        if($this->getRequest()->isPost()){
            if(!empty($errors=$ads->create())){
               $this->render('users/newAds',['dbinfo'=>$category->getAllCategories()],$errors);
            }else{
                header("Location: http://{$_SERVER['HTTP_HOST']}/profile");
                $this->render('users/profile',['dbinfo'=>$ads->getAdsByUserId($users->getUid()),'info'=>'Ads successfuly created']);
            }
        }else{
            $this->render('users/newAds',['dbinfo'=>$category->getAllCategories()]);
        }
    }

    public function editAction(){
        $users=new Users();
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
                $this->render('users/edit-ads',['dbinfo'=>$ads->getAdsById($params[0])],$errors);
            }else{
                header("Location: http://{$_SERVER['HTTP_HOST']}/profile");
                $this->render('users/profile',['dbinfo'=>$ads->getAdsByUserId($users->getUid()),'info'=>'Ads successfuly edited']);
            }
        }else{
            $this->render('users/edit-ads',['dbinfo'=>$ads->getAdsById($params[0])]);
        }
    }

    public function deleteAction(){
        $users=new Users();
        $category=new Category();
        $ads=new Ads($category,$users);
        $params=$this->getRequest()->getParams();
        $errors=array();


        if(empty($users->getUid())){
            header("Location: http://{$_SERVER['HTTP_HOST']}/");
            return;
        }


        $ads->delete($params[0]);

        header("Location: http://{$_SERVER['HTTP_HOST']}/profile");
        $this->render('users/profile',['dbinfo'=>$ads->getAdsByUserId($users->getUid()),'info'=>'Ads successfuly deleted']);

    }

} 