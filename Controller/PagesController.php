<?php
ob_start();
App::uses('AppController', 'Controller');

class PagesController extends AppController {

    public $components = array('Session', 'Auth', 'RequestHandler');
    public $helpers = array('Html', 'Form', 'Session');
    
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('membership'));
    }
    
    /* Admin End defined method */
    public function admin_index() {
        //$this->loadModel('Page');
        //$this->get_authorize('pages/index');
        $pageArr = $this->Page->find('all');
        $this->set('pages', $pageArr);
    }

    public function admin_add() {
        $this->loadModel('Page');
        $this->get_authorize('pages/add');
        if (!empty($this->request->data)) {
            $this->Page->set($this->data);
            if ($this->Page->validates()) {
                if ($this->Page->save($this->request->data)) {
                    $this->Session->setFlash(__('Page created successfully...'));
                    $this->redirect(array('action' => 'index', 'admin' => true));
                } else {
                    $this->Session->setFlash(__('Please try again later.'));
                }
            }
        }
    }

    public function admin_edit($id=null) {
        $this->get_authorize('pages/edit');
        $this->loadModel('Page');
        if (!empty($this->request->data)) {
            $this->Page->set($this->data);
            if ($this->Page->validates()) {
                if ($this->Page->save($this->request->data)) {
                    $this->Session->setFlash(__('Page updated successfully...'));
                    $this->redirect(array('controller' => 'pages', 'action' => 'admin_index'));
                } else {
                    $this->Session->setFlash(__('Please try again later.'));
                }
            }
        }
        $this->data = $this->Page->find('first', array('conditions' => array('Page.id' => $id)));
    }
    
    public function admin_delete($id = null) {
        $this->get_authorize('pages/delete');
        $this->loadModel('Page');
        $this->Page->id = $id;
        if (!$this->Page->exists()) {
            throw new NotFoundException(__('Invalid Membership'));
        }
        if ($this->Page->delete($id, true)) {
            $this->Session->setFlash(__('Page deleted successfully...'));
            $this->redirect(array('action' => 'index', 'admin' => true));
        }
        $this->Session->setFlash(__('Page are not deleted. Please try again.'));
        $this->redirect(array('action' => 'index', 'admin' => true));
    }

    /* Front End defined method */
    public function about() {}
    
    public function index($slug) {
        $this->loadModel('Page');
        $page_data = $this->Page->find('first', array('conditions' => array('Page.slug' => $slug), 'fields' => array('title','description','created')));
        $this->set('page_data',$page_data);
    }
    
}
