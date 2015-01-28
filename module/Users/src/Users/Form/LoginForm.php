<?php
namespace Users\Form;
use Zend\Form\Form;

class LoginForm extends  Form
{
    public function __construct($name = null)
    {
        parent::__construct('Login');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add([
            'name' => 'login',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Login',
            ],
        ]);
        $this->add([
            'name' => 'password',
            'required' => true,
            'attributes' => [
                'type' => 'password',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Password',
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'attributes' => [
                'type' => 'submit',
                'id' => 'submit-button',
                'value' => 'Sign in',
                'class' => 'btn btn-default',
            ]
        ]);
        $this->add(array(
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'rememberMe',
                'options' => array(
                    'label' => 'Remember Me',
                    'checked_value' => true,
                    'unchecked_value' => false,
                )
        ));
    }
}