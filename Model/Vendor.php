<?php

App::uses('AppModel', 'Model');

class Vendor extends AppModel {

    var $useTable = 'vendors';
    public $primaryKey = 'id';
    public $actsAs = array('Containable');

}
?>