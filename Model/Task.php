<?php
App::uses('AppModel', 'Model');
class Task extends AppModel {

	var $useTable  = 'Tasks';
	public $primaryKey = 'TaskID';
	public $actsAs = array('Containable');
    
    public $hasMany = array(
        'TaskHistory' => array(
            'className' => 'TaskHistory',
            'foreignKey' => 'TaskID'
        )
    );
    
    public $belongsTo = array(
        'Project' => array(
            'className' => 'Project',
            'foreignKey' => 'ProjectID'
        )
    );
    
    var $validate = array(
        'Name' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter task name'
            )
        ),
        'ProjectID' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select the project'
            )
        ),
        'StartDate' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Choose start date'
            )
        ),
        'EndDate' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Choose end date'
            )
        ),
    );
	
}