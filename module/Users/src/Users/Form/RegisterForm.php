<?php
namespace Users\Form;

use Zend\Form\Form;

class RegisterForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Register');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add([
            'name' => 'name',
            'required' => true,
            'attributes' => [
                'type' => 'text',
            ],
            'options' => [
                'label' => 'Full name',
            ],
            'filters' => [
                ['name' => 'StripTags'],
            ],
            ]);
        $this->add([
            'name' => 'login',
            'required' => true,
            'attributes' => [
                'type' => 'text',
            ],
            'options' => [
                'label' => 'Login',
            ],
            'filters' => [
                ['name' => 'StripTags'],
            ],
        ]);
        $this->add([
            'name' => 'email',
            'attributes' => [
                'type' => 'email',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Email',
            ],
            'filters' => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'EmailAddress',
                    'options' => [
                        'messages' => [
                            \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Email address format is invalid',
                        ],
                    ],
                ]
            ],
        ]);
        $this->add([
            'name' => 'password',
            'options' => [
                'label' => 'Password',
            ],
            'attributes' => [
                'type' => 'password',
            ],
            'filters' => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
        ]);
        $this->add([
            'name' => 'confirm_password',
            'options' => [
                'label' => 'Confirm password',
            ],
            'attributes' => [
                'type' => 'password',
            ],
            'filters' => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'encoding' => 'UTF-8',
                        'mib' => 3,
                        'max' => 255,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Registered',
                'id' => 'submit-button',
            ],
        ]);

    }
}