<?php

App::uses('AppModel', 'Model');

class Gallery extends AppModel {

    var $useTable = 'PhotoGalleries';
    public $primaryKey = 'GalleryID';
    public $actsAs = array('Containable');
    public $hasMany = array(
        'Photo' => array(
            'className' => 'Photo',
            'foreignKey' => 'GalleryID',
            'fields' => array('GalleryID', 'DateUploaded', 'Caption', 'FileName'),
            'conditions' => array('Photo.Deleted' => 0)
        ),
        'Forum' => array(
            'className' => 'Forum',
            'foreignKey' => 'ProjectID',
            'conditions' => array('Forum.type' => 'galleries')
        )
    );

}
