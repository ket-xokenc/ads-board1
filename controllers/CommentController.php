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
            $data = $comment->addComment();
            if ($data and $data['status'] == 'ok') {
                echo json_encode(array('status'=>1,'html'=>AdsController::commentsToTemplate($data, false)));
            } else {
                echo '{"status":0,"errors":'.json_encode($data).'}';
            }
        }
    }

    public function markup($comment)
    {
        ob_start();
        include '../views/coment/comment_template.phtml';
        $html = ob_get_clean();
        return $html;
    }
}