<?php


namespace application\classes;


class Validator {

    private  $rules=array();
    private  $message = array();

    public function setMessages(array $messages){
        $this->message=$messages;
    }

    public function setRules(array $rules){
        $this->rules=$rules;
    }

    public function setRule($name,$key,$value){
        $this->rules[$name][$key]=$value;
    }

    public function setMessage($name,$key,$value){
        $this->message[$name][$key]=$value;
    }

    public  function validate($data){
        $validErrors=array();
        $rules=$this->rules;
        $message=$this->message;
        $err=false;

        foreach($data as $key=>$val){

        /*    if(empty($data[$key])&&empty((bool)$rules[$key]['required'])){
                continue;
            }*/

            if(!empty($rules[$key])){

                switch($rules[$key]['type']){

                    case 'email':{
                        if (!filter_var($data[$key], FILTER_VALIDATE_EMAIL)) {
                            $validErrors[$key]=$message[$key]['validation'];
                        }
                        break;
                    }

                    case 'password':{
                        if(!preg_match('~^[0-9A-Za-z!@#$%*]+$~',$data[$key])){
                            $validErrors[$key]=$message[$key]['validation'];
                        }
                        break;
                    }

                    case 'login':{
                        if(!preg_match('/^[a-zA-Z0-9_-]+$/', trim($data[$key]))){
                            $validErrors[$key]=$message[$key]['validation'];
                        }
                        break;
                    }
                    case 'phone':{
                        if(!preg_match('/^\+38\d+$/', trim($data[$key]))){
                            $validErrors[$key]=$message[$key]['validation'];
                        }
                        break;
                    }
                    case 'name':{
                        if(!preg_match('/^[a-zA-Z]+$/', trim($data[$key]))){
                            $validErrors[$key]=$message[$key]['validation'];
                        }
                        break;
                    }
                    case 'checkbox':
                    case 'select':{
                        if(is_array($data[$key])){
                            foreach($data[$key] as $v){
                                if(!in_array($v,$rules[$key]['match_collection'],true)){
                                    $validErrors[$key]=$message[$key]['validation'];
                                }
                            }
                        }else{
                            if(!in_array($data[$key],$rules[$key]['match_collection'],true)){
                                $validErrors[$key]=$message[$key]['validation'];
                            }
                        }
                        break;
                    }
                    default:{

                    }
                }
            }else{
                $validErrors[$key]='Invalid form';
            }

            if(!empty($validErrors[$key])){
                $err=true;
            }
        }

        if($err){
            return $validErrors;
        }


        foreach($data as $key=>$val){
            if(!empty($rules[$key]['maxlength'])){
                if(strlen($data[$key])>$rules[$key]['maxlength']){
                    $validErrors[$key]=$message[$key]['maxlength'];
                    return $validErrors;
                }
            }
            if(!empty($rules[$key]['minlength'])){
                if(strlen($data[$key])<$rules[$key]['minlength']){
                    $validErrors[$key]=$message[$key]['minlength'];
                    return $validErrors;
                }
            }
            if(!empty($rules[$key]['forbidden'])){
                foreach($rules[$key]['forbidden'] as $value){
                    if(strripos($data[$key],$value)!==false){
                        $validErrors[$key]=$message[$key]['forbidden'];
                        return $validErrors;
                    }
                }
            }
        }

        if($err){
            return $validErrors;
        }

        return true;
    }
}