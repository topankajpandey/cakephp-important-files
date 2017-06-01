<?php

App::uses('AppModel', 'Model');

class Membership extends AppModel {

    var $useTable = 'memberships';
    public $primaryKey = 'id';
    public $actsAs = array('Containable');

    var $validate = array(
        'title' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter title'
            )
        ),
        'price' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter price'
            )
        ),
        'description' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter description'
            )
        ),
        'status' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select status'
            )
        )
    );
}
