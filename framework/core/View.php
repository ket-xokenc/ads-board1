<?php
namespace application\core;
class View
{
    public function url($controllerName, $arr = array())
    {
        $action = array_shift($arr);
        $params = '';
        if(!empty($arr))
        {
            foreach($arr as $param)
            $params .= $param;
        }
        $url = $controllerName.'/'.$action.'/'.$params;
        return $url;
    }
}