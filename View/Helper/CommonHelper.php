<?php

App::uses('Helper', 'View');

class CommonHelper extends Helper {

    function ajax_loader($invisible=false) {
        
        if($invisible){
           return false;
        }
        return '<div class="ajax_loading_container"><div class="inner"><img src="'.$this->webroot.'img/8.gif" width="50"> Loading...</div><div class="background"></div></div>';
    }
    
    function projectManagerMember($personArr=[], $type=false){
        $person_name = "";
        foreach($personArr as $key =>  $person){
            $first_name = (isset($person['User']['FirstName']))?$person['User']['FirstName']:'';
            $last_name = (isset($person['User']['LastName']))?$person['User']['LastName']:'';
            $full_name = $first_name.' '.$last_name;
            if($key==0){
                $person_name .= $full_name;
            }else{
                $person_name .= ', '.$full_name;
            }
        }
        return $person_name;
    }

}
