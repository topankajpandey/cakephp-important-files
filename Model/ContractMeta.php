<?php
App::uses('AppModel', 'Model');
class ContractMeta extends AppModel {

    var $useTable = 'contract_meta';
    public $primaryKey = 'id';
    public $actsAs = array('Containable');

}
