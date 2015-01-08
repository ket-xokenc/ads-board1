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
        'profile' => 'users/profile',
        'profile/edit' => 'users/edit',
        'profile/add-ads' => 'ads/create',
        'profile/add-img' => 'img/add',
        'profile/edit-img/([0-9]+)' => 'img/edit/$1',
        'profile/ads-edit/([0-9]+)' => 'ads/edit/$1',
        'profile/ads-delete/([0-9]+)' => 'ads/delete/$1',
        'admin' => 'admin/panel',
        'admin/show/([0-9]+)' => 'admin/show/$1',
        'admin/ban/([0-9]+)' => 'admin/ban/$1',
        'admin/unban/([0-9]+)' => 'admin/unban/$1',
        'admin/search' => 'admin/search/$1',
    ]
];