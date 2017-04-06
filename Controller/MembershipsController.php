<?php
ob_start();
App::uses('AppController', 'Controller');

class MembershipsController extends AppController {

    public $uses = array();
    public $components = array('Session', 'Auth', 'RequestHandler', 'Custom');
    public $helpers = array('Html', 'Form', 'Session');

    public function admin_index() {
        $this->get_authorize('memberships/index');
        $this->loadModel('Membership');
        $memberArr = $this->Membership->find('all');
        $this->set('membership', $memberArr);
    }

    public function admin_add() {
        $this->get_authorize('memberships/add');
        $this->loadModel('Membership');
        if (!empty($this->request->data)) {
            $this->Membership->set($this->data);
            if ($this->Membership->validates()) {
                if ($this->Membership->save($this->request->data)) {
                    $this->Session->setFlash(__('Membership created successfully...'));
                    $this->redirect(array('action' => 'index', 'admin' => true));
                } else {
                    $this->Session->setFlash(__('Please try again later.'));
                }
            }
        }
    }

    public function admin_edit($id = null) {
        $this->get_authorize('memberships/edit');
        $this->loadModel('Membership');
        if (!empty($this->request->data)) {
            $this->Membership->set($this->data);
            if ($this->Membership->validates()) {
                if ($this->Membership->save($this->request->data)) {
                    $this->Session->setFlash(__('Membership updated successfully...'));
                    $this->redirect(array('action' => 'index', 'admin' => true));
                } else {
                    $this->Session->setFlash(__('Please try again later.'));
                }
            }
        }
        $this->data = $this->Membership->find('first', array('condtion' => array('Membership.Id')));
    }

    public function admin_delete($id = null) {
        $this->get_authorize('memberships/delete');
        $this->loadModel('Membership');
        $this->Membership->id = $id;
        if (!$this->Membership->exists()) {
            throw new NotFoundException(__('Invalid Membership'));
        }
        if ($this->Membership->delete($id, true)) {
            $this->Session->setFlash(__('Membership deleted successfully...'));
            $this->redirect(array('action' => 'index', 'admin' => true));
        }
        $this->Session->setFlash(__('Membership are not deleted. Please try again.'));
        $this->redirect(array('action' => 'index', 'admin' => true));
    }
 
    public function admin_activate($id = null) {
        $this->get_authorize('memberships/activate');
        $this->autoRender = false;
        $this->loadModel('Membership');
        $this->Membership->id = $id;
        if (!$this->Membership->exists()) {
            throw new NotFoundException(__('Invalid Membership'));
        }
        if ($this->Membership->updateAll(
                        array('Membership.status' => '0'), array('Membership.id' => $id)
                )) {
            $this->Session->setFlash(__('Membership activated successfully...'));
            $this->redirect(array('action' => 'index', 'admin' => true));
        }
        $this->Session->setFlash(__('Please try again later.'));
        $this->redirect(array('action' => 'index', 'admin' => true));
    }

    public function admin_deactivate($id = null) {
        $this->get_authorize('memberships/deactivate');
        $this->autoRender = false;
        $this->loadModel('Membership');
        $this->Membership->id = $id;
        if (!$this->Membership->exists()) {
            throw new NotFoundException(__('Invalid Membership'));
        }
        if ($this->Membership->updateAll(
                        array('Membership.status' => '1'), array('Membership.id' => $id)
                )) {
            $this->Session->setFlash(__('Membership deactivated successfully...'));
            $this->redirect(array('action' => 'index', 'admin' => true));
        }
        $this->Session->setFlash(__('Please try again later.'));
        $this->redirect(array('action' => 'index', 'admin' => true));
    }

}
