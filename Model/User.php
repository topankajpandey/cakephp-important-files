<?php

App::uses('AppModel', 'Model');

class User extends AppModel {

    var $useTable = 'Members';
    public $primaryKey = 'MemberID';
    public $actsAs = array('Containable');

    /**
     * method called beforeSave
     */
    public function beforeSave($options = array()) {
        if (isset($this->data[$this->alias]['password'])) {
            $this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
        }
        return true;
    }

    public $hasMany = array(
        'ProjectMember' => array(
            'className' => 'ProjectMember',
            'foreignKey' => 'MemberID',
            'fields' => array('ProjectID')
        ), 
        'ForumInvite' => array(
            'className' => 'ForumInvite',
            'foreignKey' => 'user_id'
        )
        
    );
    public $hasOne = array(
        'Permission' => array(
            'className' => 'Permission',
            'foreignKey' => 'user_id',
            'fields' => array('actions', 'user_id')
        ),
        'Subscription' => array(
            'className' => 'Subscription',
            'foreignKey' => 'user_id',
            'conditions' => array('Subscription.status' => 1)
        )
    );
        
    var $validate = array(
        'FirstName' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter first name'
            )
        ),
        'LastName' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter last name'
            )
        ),
        'email' => array(
            'NotEmpty' => array(
                'rule' => array('email'),
                'on' => 'create',
                'message' => 'Enter valid email address'
            )

        /* 'unique' => array(
          'rule' => 'isUniqueName',
          'on' => 'create',
          'message' => 'Email is already exist in our records'
          ) */
        ),
        'password' => array(
            'rule' => array('minLength', '5'),
            'message' => 'Password must be at least 5 characters long'
        ),
        'ProfDes1' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter Prof Des1'
            )
        ),
        'Address1' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter Address1'
            )
        ),
        'City' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter City'
            )
        ),
        'Province' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter Province'
            )
        ),
        'Country' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Enter Country'
            )
        ),
        'CountryCode' => array(
            'NotEmpty' => array(
                'rule' => array('maxLength', '2'),
                'message' => 'Enter CountryCode'
            )
        ),
        'PostalCode' => array(
            'NotEmpty' => array(
                'rule' => array('maxLength', '8'),
                'message' => 'Enter PostalCode'
            )
        ),
        'Sex' => array(
            'NotEmpty' => array(
                'rule' => array('notBlank'),
                'message' => 'Select Gender'
            )
        ),
        'username' => array(
            'NotEmpty' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter username.'
            ),
            'alphaNumeric' => array(
                'rule' => 'alphaNumeric',
                'message' => 'Only alphabets and numbers allowed',
            ),
            'IsUnique' => array(
                'rule' => 'isUnique',
                'message' => 'Username is already exist in our records'
            )
        ),
        'old_password' => array(
            'rule' => 'checkCurrentPassword',
            'message' => 'Old password that you have entered is wrong'
        )
    );

    public function checkCurrentPassword($data) {
        $this->id = AuthComponent::user('MemberID');
        $password = $this->field('password');
        return(AuthComponent::password($data['old_password']) == $password);
    }

    public function isUniqueName($fields) {
        return true;
    }

}
