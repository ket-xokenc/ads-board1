<?php
return [
    'acl'=>[
        'ads'=>[
            'edit'=>'user',
            'delete'=>'user',
            'create'=>'user',
        ],
        'users'=>[
           'profile' => 'user',
        ],
    ],


    'hierarchy'=>['guest','user','admin']
];