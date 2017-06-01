<?php

App::uses('AppModel', 'Model');

class Subscription extends AppModel {

    var $useTable = 'subscriptions';
    public $primaryKey = 'id';
    public $actsAs = array('Containable');
    
    public $belongsTo = array(
        'Membership' => array(
            'className' => 'Membership',
            'foreignKey' => 'membership_id'
        )
    );

}

?>