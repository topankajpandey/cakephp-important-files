<?php

App::uses('AppModel', 'Model');

class Forum extends AppModel {

    var $useTable = 'Forum';
    public $primaryKey = 'ForumPostID';
    public $actsAs = array('Containable');
    
    public $belongsTo = array(
        'Project' => array(
            'className' => 'Project',
            'foreignKey' => 'ProjectID'
        ),
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'PostedBy'
        )
    );
    var $validate = array(
        'Subject' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter any subject'
            )
        ),
        'Message' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter the message'
            )
        )
    );

}
