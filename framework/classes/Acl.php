<?php

namespace application\classes;

class Acl {
    public function access($controller,$action){
        $acl=Config::get('acl');
        $hier=Config::get('hierarchy');
        $role=Session::get('role')?:'guest';

        $controller=strtolower(str_ireplace("Controller", "", $controller));
        $action=strtolower(str_ireplace("Action", "", $action));

        if((empty($acl[$controller])||
             $acl[$controller][$action]===$role)||
              ($acl[$controller][$action]==='guest')||
               array_search($role, $hier)>array_search($acl[$controller][$action], $hier)){
           return true;
        }

        return false;
    }
} 