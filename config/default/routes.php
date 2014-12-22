<?php
return [
    'routes' => [
        'home' => 'home/index',
        'users/([-_a-z0-9]+)' => 'users/$1',
        'users/([-_a-z0-9]+)/([-_a-z0-9]+)' => 'users/$1/$2',
        'login' => 'users/login',
        'login/([-_a-z0-9]+)' => 'users/login/$1',
        'registration' => 'users/registration',
        'search' => 'home/index',
        'logout' => 'users/logout',
        'restore-password' =>'users/restorePassword',
        'confirmation/hash/([a-zA-Z0-9]+)' => 'users/confirmation/$1',
        'about' => 'home/staticPage/about',
        'payment-plan' => 'users/paymentPlan',
        'admin' => 'admin/panel',
        'admin/show/([-_0-9]+)' => 'admin/show/$1',
        'admin/ban/([-_0-9]+)' => 'admin/ban/$1',
    ]
];