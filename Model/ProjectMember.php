<?php
App::uses('AppModel', 'Model');
class ProjectMember extends AppModel {

	var $useTable  = 'ProjectMembers';
	
	var $belongsTo = array(
		'User' => array(
			'className' => 'User',
            'foreignKey' => 'MemberID',
			'fields' => array('MemberID', 'FirstName', 'LastName', 'Email', 'ProfilePic')
		)
	);
	
}