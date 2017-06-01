<?php

App::uses('AppModel', 'Model');

class Contract extends AppModel {

    var $useTable = 'contracts';
    public $primaryKey = 'id';
    public $actsAs = array('Containable');

    public function beforeSave($options = array()) {
        if (isset($this->data[$this->alias]['created'])) {
            $this->data[$this->alias]['created'] = date('Y-m-d H:i:s');
        }
        return true;
    }
    
    

    var $validate = array(
        'title' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter contract title'
            )
        ),
        'contract_type' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select the contract type'
            )
        ),
        'department_id' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select the department'
            )
        ),
        'project_id' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select the project'
            )
        ),
        'proposal_id' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select the proposal'
            )
        ),
        'vendor_id' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select the vendor'
            )
        ),
        'start_date' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select start date'
            )
        ),
        'end_date' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select end date'
            )
        ),
        'description' => array(
            'NotEmpty' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter contract description'
            ),
            'minLength' => array(
                'rule' => array('minLength', '50'),
                'message' => 'Description must be at least 50 characters long'
            )
        ),
         'comodity_code' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter comodity code'
            )
        ),
        'status' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select project status'
            )
        ),
        'auto_renew' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select yes or no'
            )
        ),
        'extendable' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select yes or no'
            )
        )
    );

}
