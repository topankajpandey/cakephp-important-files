<?php

App::uses('AppModel', 'Model');

class Contact extends AppModel {

    var $useTable = 'Contacts';
    public $primaryKey = 'ContactID';
    public $actsAs = array('Containable');
    
}
