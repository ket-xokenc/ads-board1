<?php
return [
    'acl' => [
        'allow' => [
            [['guest'], 'Users\Controller\Users'],
            [['user'], 'Users\Controller\Users'],
            [['admin'], null],
        ],
        'deny' => [
            //[['user'], 'Users\Controller\Users'],
        ],
    ],
];