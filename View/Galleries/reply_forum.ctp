<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">
        <h1 class="p-l-0">Reply Forum</h1>
        <?php
        echo $this->Form->create('Forum', array(
            'name' => 'reply_document_forum',
            'enctype' => 'multipart/form-data'
        ));
        ?>
        <div class="ionic-table edit-form">

            <div class="row">

                <div class="col-xs-12 form-group">
                    <label for="folder_project_id">Subject*</label>
                    <?php
                    echo $this->form->input('Forum.Subject', array(
                        'class' => 'form-control',
                        'required' => false,
                        'value' => 'Re: ' . $forumData['Forum']['Subject'],
                        'placeholder' => 'Enter Subject',
                        'type' => 'text',
                        'label' => false,
                            )
                    );
                    ?>
                </div>

                <div class="col-xs-12 form-group">
                    <label for="folder_project_id">Message*</label>
                    <?php
                    echo $this->Form->input('Forum.Message', array(
                        'class' => 'form-control',
                        'required' => false,
                        'value' => '',
                        'type' => 'textarea',
                        'placeholder' => 'Enter description....',
                        'label' => false
                            )
                    );
                    ?>
                </div>

                <div class="edit-action checkbox-btn possition-input"><hr> 

                    <?php echo $this->Form->input('Forum.PostedDate', array('type' => 'hidden', 'value' => date('Y-m-d H:i:s'))); ?>
                    <?php echo $this->Form->input('Forum.Thread', array('type' => 'hidden')); ?>
                    <?php echo $this->Form->input('Forum.ProjectID', array('type' => 'hidden')); ?>
                    <?php echo $this->Form->input('Forum.type', array('type' => 'hidden', 'value' => 'galleries')); ?>
                   <?php echo $this->Form->input('Forum.Level', array('type' => 'hidden', 'value' => ($this->data['Forum']['Level'] + 1))); ?>
                    <?php echo $this->Form->input('Forum.PostedBy', array('type' => 'hidden', 'value' => $this->Session->read('Auth.User.MemberID'))); ?>
                    <?php echo $this->Form->input('Forum.Archived', array('type' => 'hidden', 'value' => 0)); ?>
                    
                    <?php echo $this->Form->submit('Submit', array('class' => 'edit')); ?>
                    <?php echo $this->Html->link('Cancel', array('controller' => 'documents', 'action' => 'index'), array('class' => 'logout', 'escape' => false)); ?>
                </div>
            </div>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
</div>
<link href='<?php echo $this->webroot; ?>css/contracts/contract.css' rel='stylesheet'/> 
