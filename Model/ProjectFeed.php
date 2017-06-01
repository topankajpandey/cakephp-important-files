<?php

App::uses('AppModel', 'Model');

class ProjectFeed extends AppModel {

    var $useTable = 'ProjectFeeds';
    public $primaryKey = 'ProjectFeedID';
    public $actsAs = array('Containable');
    var $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'InitiatorID',
            'fields' => array('MemberID', 'FirstName', 'LastName', 'Email', 'ProfilePic')
        ),
        'Project' => array(
            'className' => 'Project',
            'foreignKey' => 'ProjectID',
        )
    );

}

?>