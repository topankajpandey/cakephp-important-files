<?php

App::uses('AppModel', 'Model');

class Page extends AppModel {

    var $useTable = 'pages';
    public $primaryKey = 'id';
    public $actsAs = array('Containable');
    var $validate = array(
        'title' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter title'
            )
        ),
        'slug' => array(
            "checkUnique" => array(
                "rule" => array("checkUnique"),
                "message" => "Slug already exists."
            )
        ),
        'description' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter description'
            )
        ),
        'status' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select status'
            )
        )
    );

    public function checkUnique() {
        $condition = array('Page.slug' => $this->data["Page"]["slug"]);

        if (isset($this->data["Page"]["id"])) {
            $condition["Page.id <>"] = $this->data["Page"]["id"];
        }
        $result = $this->find("count", array("conditions" => $condition));
        return ($result == 0);
    }

}
