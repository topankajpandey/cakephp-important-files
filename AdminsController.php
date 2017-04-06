<?php

ob_start();
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class AdminsController extends AppController {

    public $components = array('Session', 'Auth', 'RequestHandler');
    public $helpers = array('Html', 'Form', 'Session');

    public function beforeFilter() {

        parent::beforeFilter();
        $this->Auth->allow(array('get_email','admin_reset','admin_add_admin','admin_login','admin_profile_image','admin_checkemailalreadyexist','admin_delete_bussiness','get_member'));
    }
	
	
	 public function admin_login(){ 
	 
  $this->layout ='admin';
  $this->loadModel('User');
	if ($this->request->is('Post')) {	
//echo "<pre>";print_r($this->data);exit;	
            App::Import('Utility', 'Validation');
            if (isset($this->data['User']['username']) && Validation::email($this->data['User']['username'])){
                $this->request->data['User']['email'] = $this->data['User']['username'];
                $this->Auth->authenticate['Form']     = array(
                    'fields' => array(
						'userModel' => 'User',
                        'username' => 'email',
						'password' => 'password'
                    )
                );
                $x = $this->User->find('first',array('conditions' => array('email' => $this->data['User']['username'])));
				//echo "<pre>";print_r($x);exit;
            } else {
                $this->Auth->authenticate['Form'] = array(
                    'fields' => array(
						'userModel' => 'User',
                        'username' => 'username',
						'password' => 'password'
                    )
                ); 
                $x = $this->User->find('first',array('conditions' => array('username' => $this->data['User']['username'])));
			}
			if(!empty($x)){
            if($x['User']['usertype_id'] == '5' && $x['User']['status'] == '1'){
            	if (!$this->Auth->login()) {
            		$this->Session->setFlash('Invalid username or password.');
            		$this->redirect(array('controller' => 'users', 'action' => 'admin_login'));
            	} else {
            		$this->Session->write('VenueUser',true);
            		//$this->Session->setFlash('Successfully signed in');
            		$this->redirect(array('controller' => 'users', 'action' => 'admin_dashboard'));
            	}         
            }else{
            	$this->Session->setFlash("You don't have Administrator authorities or your account is inactive.");
            	$this->redirect(array('controller' => 'users', 'action' => 'admin_login'));
            }
			  } else {
					$this->Session->setFlash("Invalid username or password.");
					$this->redirect(array('controller' => 'users', 'action' => 'admin_login'));
			}
		}
  }

  

}

?>