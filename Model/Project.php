<?php

App::uses('AppModel', 'Model');

class Project extends AppModel {

    var $useTable = 'Projects';
    public $primaryKey = 'ProjectID';
    public $actsAs = array('Containable');
    var $validate = array(
        'Name' => array(
            'NotEmpty' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter project name'
            ),
            'IsUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This project name is already exist in our records'
            )
        ),
        'Description' => array(
            'NotEmpty' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter project description'
            ),
            'minLength' => array(
                'rule' => array('minLength', '50'),
                'message' => 'Description must be at least 50 characters long'
            )
        ),
        'completed' => array(
            'NotEmpty' => array(
                'rule' => 'notBlank',
                'message' => 'Please select status'
            )
        )
    );
    public $hasMany = array(
        'Forum' => array(
            'className' => 'Forum',
            'foreignKey' => 'ProjectID'
        ),
        'ProjectManager' => array(
            'className' => 'ProjectManager',
            'foreignKey' => 'ProjectID'
        ),
        'ProjectMember' => array(
            'className' => 'ProjectMember',
            'foreignKey' => 'ProjectID'
        ),
        'ProjectFeed' => array(
            'className' => 'ProjectFeed',
            'foreignKey' => 'ProjectID'
        ),
        'Task' => array(
            'className' => 'Task',
            'foreignKey' => 'ProjectID'
        ),
        
        'Gallery' => array(
            'className' => 'Gallery',
            'foreignKey' => 'ProjectID'
        ),
        'Documents' => array(
            'className' => 'Document',
            'foreignKey' => 'ProjectID'
        ),
        'Folders' => array(
            'className' => 'Folder',
            'foreignKey' => 'ProjectID'
        ),
		 'Contract' => array(
            'className' => 'Contract',
            'foreignKey' => 'project_id'
        )
		
    );

}
