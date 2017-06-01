<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property AccessToken $AccessToken
 * @property AuthCode $AuthCode
 * @property Client $Client
 * @property RefreshToken $RefreshToken
 */
class Admin extends AppModel {
var $name = 'User';
		var $actsAs = array("Containable");
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'email';
/**
 * method called beforeSave
 */	
	public function beforeSave($options = array()){
	if(isset($this->data[$this->alias]['password'])){
			$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
		}  
		return true;
	}
}
