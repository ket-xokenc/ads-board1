<?php
namespace Users\Form;

use Zend\InputFilter\InputFilter;

class RegisterFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'email',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'domain' => true,
                    ),
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 3,
                        'max' => 255,
                    ),
                ),
            ),
        ));
        $this->add(array(
            'name' => 'name',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags',
                ),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 255,
                    ),
    )           ,
            ),
        ));
        $this->add(array(
            'name' => 'login',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags',
                ),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 255,
                    ),
                )           ,
            ),
        ));
        $this->add(array(
            'name' => 'password',
            'required' => true,
            'validator' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 3,
                        'max' => 32,
                    ],
                ],
            ],
        ));
        $this->add(array(
        'name' => 'confirm_password',
        'required' => true,
            'validator' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 3,
                        'max' => 32,
                    ],
                ],
            ],
    ));
    }
}