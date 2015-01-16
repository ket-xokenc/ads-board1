<?php
namespace Users\Form;

use Zend\InputFilter\InputFilter;

class LoginFilter extends InputFilter
{
    public function __construct()
    {
        $this->add([
            'name' => 'login',
            'required' => true,
            'validator' => [
                ['name' => 'StringLength',
                    'options' => [
                        'min' => 3,
                        'max' => 32
                    ],
                ],
            ],
            'filters' => [
                ['name' => 'StripTags']
            ],
        ]);
        $this->add([
            'name' => 'password',
            'required' => true,
            'filters' => [
                ['name' => 'StripTags']
            ],
            'validatirs' => [
                ['name' => 'StringLength',
                'options' => [
                    'min' => 3,
                    'max' => 255,
                ],
                ]
            ],
        ]);
        $this->add([
            'name' => 'rememberMe',
            'required' => false,
        ]);
    }
}