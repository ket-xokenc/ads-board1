<?php
/**
 * Created by PhpStorm.
 * User: alexandr
 * Date: 09.12.14
 * Time: 14:51
 */
class UsersController extends BaseController
{
    public function loginAction()
    {
        $model = new Users();
//        $user = $model->getById(1);
        $user = $model->getByLoginAndPassw('alex', '123');
        //print_r($user);
        $this->render('users/login');

    }

    public function registrationAction()
    {
        $this->render('users/registration');
    }

}