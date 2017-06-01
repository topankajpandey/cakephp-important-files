<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">
        <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                <h1 class="p-l-0 contract-forum-view-button">Rename Folder
                    <?php echo $this->Html->link($this->Html->tag('span', '', array('class' => 'fa fa-arrow-left')), array('controller' => 'documents', 'action' => 'index'), array('title' => 'Back to Documents', 'escape' => false)); ?>
                </h1>
            </div>
        </div>
        
        <?php
        echo $this->Form->create('Folder', array(
            'name' => 'RenameFolder'
        ));
        ?>

        <div class="ionic-table edit-form">
            <div class="ionic-form">
                <?php
                    echo $this->Form->input('Name', array(
                        'id' => 'rename_folder',
                        'type' => 'text',
                        'class' => 'input_txt',
                        'placeholder' => '',
                        'value' => $folderData['Folder']['Name'],
                        'label' => 'Name'
                    )
                );
                ?>
            </div>
        </div>
        <div class="edit-action checkbox-btn"> 
            <?php echo $this->Form->input('FolderID', array('type' => 'hidden', 'value' => $folderData['Folder']['FolderID'])); ?>
            <?php echo $this->Form->input('ChangedDate', array('type' => 'hidden', 'value' => date('Y-m-d H:i:s'))); ?>
            <?php echo $this->Form->submit('Submit', array('class' => 'edit')); ?>
            <?php echo $this->Html->link('Cancel', array('controller' => 'documents', 'action' => 'index'), array('class'=> 'logout', 'escape' => false)); ?>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
</div>
<link href='<?php echo $this->webroot; ?>css/custom.css' rel='stylesheet'/> 
<script>
    /*********************  Start Editing By T:307 ***********************/

    $.validator.setDefaults({
        submitHandler: function () {
            document.newProjectForm.submit();
        }
    });

    $().ready(function () {
        $("#DocumentRenameForm").validate();

        $("#rename_folder").rules("add", {
            required: true,
            messages: {
                required: "Please enter folder name"
            }
        });

    });

    /*********************  End Editing By T:307 ***********************/
</script>



