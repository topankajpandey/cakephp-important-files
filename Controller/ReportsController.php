<?php
ob_start();
App::uses('AppController', 'Controller');

class ReportsController extends AppController {

    public $uses = array();
    public $components = array('Session', 'Auth', 'RequestHandler', 'Custom');
    public $helpers = array('Html', 'Form', 'Session');

    public function admin_index() {
        $this->get_authorize('reports/index');
        $this->loadModel('Membership');
        $memberArr = $this->Membership->find('all');
        $this->set('membership', $memberArr);
    }
}
