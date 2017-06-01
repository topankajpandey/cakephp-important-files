<?php

App::uses('AppModel', 'Model');

class Proposal extends AppModel {

    var $useTable = 'proposals';
    public $primaryKey = 'id';
    public $actsAs = array('Containable');

}
?>

