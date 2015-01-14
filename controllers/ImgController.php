<?php
use application\core\BaseController as BaseController;
use application\classes\CustomUploadHandler;

class ImgController extends BaseController
{
    public function addAction()
    {

        $upload_handler = new CustomUploadHandler([
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
            'script_url' => '/profile/add-img',
            'max_number_of_files' => 10,
            'max_file_size' => 2000000,
            'user_dirs' => true,
        ], '/tmp_files/');

    }

    public function editAction()
    {

        $params = $this->getRequest()->getParams();

        $upload_handler = new CustomUploadHandler([
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
            'script_url' => '/profile/edit-img/' . $params[0],
            'max_number_of_files' => 10,
            'max_file_size' => 2000000,
            'user_dirs' => true,
        ], '/files/', $params);

    }
} 