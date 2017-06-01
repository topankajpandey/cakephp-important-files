<?php

App::uses('AppModel', 'Model');

class Department extends AppModel {

    var $useTable = 'departments';
    public $primaryKey = 'id';
    public $actsAs = array('Containable');
    
    public $belongsTo = array(
        'Contract' => array(
            'className' => 'Contract',
            'foreignKey' => 'departments_id',
        )
    );

}
?>