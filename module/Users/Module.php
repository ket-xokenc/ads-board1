<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Users for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Users;
use Zend\Authentication\AuthenticationService;
use Zend\Form\View\Helper\Captcha\Dumb;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Users\Model\Users;
use Users\Model\UsersTable;
use Zend\Debug\Debug;
use Users\Form\LoginForm;
class Module implements AutoloaderProviderInterface
{
    protected $acl;
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => [
                'Users\Model\UsersTable' => function($sm) {
                $tableGateway = $sm->get('UserTableGateway');
                $table = new UsersTable($tableGateway);
                return $table;
                },
                'UserTableGateway' => function($sm) {
                    $adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Users);
                    return new TableGateway('users', $adapter, null, $resultSetPrototype);
                },
                'UserAuthService' => function($sm) {
                    $adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $tableAuthAdapter = new \Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter($adapter, 'users', 'login', 'password');
                    $authService = new AuthenticationService();
                    $authService->setAdapter($tableAuthAdapter);
                    return $authService;
                },
                'LoginForm' => function($sm) {
                    $form = new \Users\Form\LoginForm();
                    $form->setInputFilter($sm->get('LoginFilter'));
                    return $form;
                },
                'LoginFilter' => function($sm) {
                    return new \Users\Form\LoginFilter();
                },
                'RegisterForm' => function($sm) {
                    $form = new \Users\Form\RegisterForm();
                    $form->setInputFilter($sm->get('RegisterFilter'));
                    return $form;
                },
                'RegisterFilter' => function($sm) {
                    return new \Users\Form\RegisterFilter();
                },
                'UserEditForm' => function($sm) {
                    $form = new \Users\Form\EditForm();
                    $form->setInputFilter($sm->get('RegisterFilter'));
                    return $form;
                },

        ],
        );
    }

    public function onBootstrap(MvcEvent $e)
    {
        // You may not need to do this if you're doing it elsewhere in your
        // application
    /*    $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);*/

        $e->getApplication()->getEventManager()->attach('route', [$this, 'checkAccess']);
    }

    public function checkAccess($e)
    {
        $this->initAcl($e);
        $route = $e->getRouteMatch();
        $session = new \Zend\Session\Container('user');
        $role = isset($session->role) ? $session->role : 'guest';
        if(!$this->acl->hasResource($route->getParam('controller'))) {
            $access = false;
        } else {
            $access = $this->acl->isAllowed($role,
                $route->getParam('controller'),
                $route->getParam('action'));
        }
//        Debug::dump($access,'Assess:');
//        Debug::dump($role);
        if(!$access) { exit('Restricted Access!'); }
    }

    public function initAcl($e)
    {
        $this->acl = new \Zend\Permissions\Acl\Acl();
        $config = $e->getApplication()
                    ->getServiceManager()
                    ->get('Config');
        $params = $config['acl'];
//        Debug::dump($params);
//        $allow = $params['allow'];
        foreach($params as $key => $value) {
            foreach ($value as $rule) {
                $rules = $rule[0];
                $resourses = (isset($rule[1])) ? $rule[1] : null;
                $privileges = (isset($rule[2])) ? $rule[2] : null;
                foreach ($rules as $role) {
                    if (!$this->acl->hasRole($role)) {
                        $this->acl->addRole($role);
                    }
                    if (is_array($resourses)) {
                        foreach ($resourses as $resourse) {
                            if (!$this->acl->hasResource($resourse)) {
                                $this->acl->addResource($resourse);
                            }
                        }
                    } elseif (is_string($resourses)) {
                        if (!$this->acl->hasResource($resourses)) {
                            $this->acl->addResource($resourses);
                        }
                    }
                    if ($key == 'allow') {
                        $this->acl->allow($role, $resourses, $privileges);
                    } elseif ($key == 'deny') {
                        $this->acl->deny($role, $resourses, $privileges);
                    }
                }

            }
        }
    }
}
