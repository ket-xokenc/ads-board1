<?php
use application\core\BaseController as BaseController;

class CommentController extends BaseController
{
    public function __contruct($request)
    {
        parent::__construct($request);
    }

    public function addCommentAction()
    {
        $errors = [];
        $comment = new Comment();
        if ($this->getRequest()->isPost()) {
            $errors = $comment->addComment();
            if ($errors and $errors['status'] == 'ok') {
                echo json_encode(array('status'=>1,'html'=>$this->markup($errors)));
            } else {
                echo '{"status":0,"errors":'.json_encode($errors).'}';
            }
        }
    }

    public function markup($data)
    {

        $str =  '
			<div class="panel panel-primary ">
                    <div class="panel-body comment-desc">
                        <p>Name: '.\application\classes\Session::get("login").'</p>
                        <p>Date: '.$data['date_create'].'</p>
                    </div>
                    <div class="panel-body text-comment">
                        '.$_POST['body'].'
                    </div>
                </div>
		';
        return $str;
    }
}