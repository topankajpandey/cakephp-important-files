<?php

App::uses('AppModel', 'Model');

class ProjectManager extends AppModel {
    
    var $useTable = 'ProjectManagers';
    public $actsAs = array('Containable');
    
    var $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'MemberID',
            'fields' => array('MemberID', 'FirstName', 'LastName', 'Email', 'ProfilePic')
        ),
        'Project' => array(
            'className' => 'Project',
            'foreignKey' => 'ProjectID',
        )
    );
    

}

?>