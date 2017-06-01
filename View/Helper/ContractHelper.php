<?php

App::uses('Helper', 'View');

class ContractHelper extends Helper {

    
    /* Add more html content for contract custom field */
    function add_more_content_custom_field($due_date_content = false) {
        if (!empty($due_date_content) && !empty($due_date_content['contract_key'])) {
            $counter = 1;
            foreach ($due_date_content['contract_key'] as $key => $contract_key) {
                $contract_value = $due_date_content['contract_value'][$key];
                $data = '<div><div class="col-xs-5 form-group"><label for="contract_key">Key:</label> <input name="contract_key[]" class="form-control" value="'.$contract_key.'" placeholder="Enter contract key" maxlength="255" type="text"> </div><div class="col-xs-6 form-group"> <label for="contract_value">Value:</label> <input name="contract_value[]" value="'.$contract_value.'" class="form-control" placeholder="Enter contract value" maxlength="255" type="text"></div>';
                if($counter==1){
                    $data .= $this->add_remove_button_custom_field('add_button');
                }else{
                     $data .= $this->add_remove_button_custom_field();
                }
                $data .= '</div>';
                echo $data;
                $counter++;
            }
        } else {
            $data = '<div><div class="col-xs-5 form-group"><label for="contract_key">Key:</label> <input name="contract_key[]" class="form-control" placeholder="Enter contract key" maxlength="255" type="text"> </div><div class="col-xs-6 form-group"> <label for="contract_value">Value:</label> <input name="contract_value[]" class="form-control" placeholder="Enter contract value" maxlength="255" type="text"></div></div>';
            return $data;
        }
    }

    /* Add more button for contract custom field */
    function add_remove_button_custom_field($type = false) {
        $data = "";
        if ($type == 'add_button') {
            $data = '<div class="col-xs-1 form-group"> <label for="folder_project_id">&nbsp;</label> <button class="add_custom_contract_button form-control">+</button> </div>';
        } else {
            $data = '<div class="col-xs-1 form-group"> <label for="folder_project_id">&nbsp;</label><button class="remove_custom_contract_field form-control">-</button></div>';
        }
        return $data;
    }
    
    /* Add more html content for due date */
    function add_more_content_due_date($due_date_content = false) {
        if (!empty($due_date_content) && !empty($due_date_content['date_key'])) {
            $counter = 1;
            foreach ($due_date_content['date_key'] as $key => $duedates) {
                $start_date = $due_date_content['start_dete'][$key];
                $end_date = $due_date_content['end_date'][$key];
                $data = '<div><div class="col-xs-5 form-group"><label for="date_key">Date Key:</label> <input name="date_key[]" class="form-control" value="'.$duedates.'" placeholder="Enter date key" maxlength="255" type="text"> </div><div class="col-xs-3 form-group"> <label for="start_date">Start Date:</label> <input name="start_date[]" value="'.$start_date.'" class="form-control forum_multiple_date" placeholder="Choose start date" maxlength="255" readonly="readonly" type="text"></div><div class="col-xs-3 form-group"> <label for="end_date">End Date:</label> <input name="end_date[]" value="'.$end_date.'" readonly="readonly" class="form-control forum_multiple_date multiple_end_date" placeholder="Choose end date" maxlength="255" type="text"></div>';
                if($counter==1){
                    $data .= $this->add_remove_button_due_date('add_button');
                }else{
                     $data .= $this->add_remove_button_due_date();
                }
                $data .= '</div>';
                echo $data;
                $counter++;
            }
        } else {
            $data = '<div><div class="col-xs-5 form-group"><label for="date_key">Date Key:</label> <input name="date_key[]" class="form-control" placeholder="Enter date key" maxlength="255" type="text"> </div><div class="col-xs-3 form-group"> <label for="start_date">Start Date:</label> <input name="start_date[]" readonly="readonly" class="form-control forum_multiple_date" placeholder="Choose start date" maxlength="255" type="text"></div><div class="col-xs-3 form-group"> <label for="end_date">End Date:</label> <input name="end_date[]" class="form-control forum_multiple_date multiple_end_date" readonly="readonly" placeholder="Choose end date" maxlength="255" type="text"></div></div>';
            return $data;
        }
    }

    /* Add more button for due date */
    function add_remove_button_due_date($type = false) {
        $data = "";
        if ($type == 'add_button') {
            $data = '<div class="col-xs-1 form-group"> <label for="folder_project_id">&nbsp;</label> <button class="add_field_button form-control">+</button> </div>';
        } else {
            $data = '<div class="col-xs-1 form-group"> <label for="folder_project_id">&nbsp;</label><button class="remove_field form-control">-</button></div>';
        }
        return $data;
    }

}
