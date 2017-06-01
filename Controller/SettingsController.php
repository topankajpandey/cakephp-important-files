<?php

ob_start();
App::uses('AppController', 'Controller');

class SettingsController extends AppController {

    public $components = array('Auth', 'RequestHandler', 'Custom');
    public $helpers = array('Html', 'Form');

    public function admin_index() {
        $this->get_authorize('settings/index');
        $this->loadModel('SettingsGlobal');
        
        if ($this->request->is('post') || $this->request->is('put')) {
            $SettingsGlobal = $this->request->data['SettingsGlobal'];
            $logo = "";
            if ($this->SettingsGlobal->validates()) {
                if (!empty($_FILES['site_logo']['name']) && $_FILES['site_logo']['error'] == 0) {
                    array_map('unlink', glob("files/settings/logo/*.*"));
                    $pth = 'files/settings/logo/' . $_FILES['site_logo']['name'];
                    move_uploaded_file($_FILES['site_logo']['tmp_name'], $pth);
                    $logo = $_FILES['site_logo']['name'];
                } else {
                    $logo = $this->request->data['old_logo'];
                }
                $SettingsGlobal['site_logo'] = $logo;
                $data = $this->SettingsGlobal->find('first');
                if(empty($data)){
                    $this->SettingsGlobal->create();
                }else{
                   $SettingsGlobal['id'] = $data['SettingsGlobal']['id']; 
                }
                
                $this->SettingsGlobal->save($SettingsGlobal);
                if ($this->SettingsGlobal->save()) {
                    $this->Session->setFlash(__('Settings updated successfully...'));
                    $this->redirect(array('controller' => 'settings', 'action' => 'index', 'admin' => true));
                }
            }
        }
       
        $this->data = $this->SettingsGlobal->find('first');
        
    }

}
