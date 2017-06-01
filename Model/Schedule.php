<?php
App::uses('AppModel', 'Model');
class Schedule extends AppModel {

	var $useTable  = 'Tasks';
	public $primaryKey = 'TaskID';
	public $actsAs = array('Containable');
	
	var $validate = array(
		'Name'=>array(
			'NotEmpty'=>array(
				'rule'=>'notBlank',
				'message'=>'Please enter email Address.'
			),
			'IsUnique'=>array(
				'rule'=>'isUnique',
				'message'=>'This project name is already exist in our records'
			)
		),
		// 'Description' => array(
			// 'NotEmpty'=>array(
				// 'rule'=>'notBlank',
				// 'message'=>'Please enter project description.'
			// ),
			// 'minLength'=>array(
				// 'rule'=> array('minLength', '50'),
				// 'message'=> 'Description must be at least 50 characters long'
			// )
        // )		
	);
	
	// public $hasMany = array(
        // 'ProjectMember' => array(
            // 'className' => 'ProjectMember',
            // 'foreignKey' => 'ProjectID'
        // ),
		// 'ProjectManager' => array(
			// 'className' => 'ProjectManager',
			// 'foreignKey' => 'ProjectID'
		// ),
		// 'ProjectFeed' => array(
			// 'className' => 'ProjectFeed',
			// 'foreignKey' => 'ProjectID'
		// )
    // );
	
}