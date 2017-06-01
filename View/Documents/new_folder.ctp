<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">
        
        <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                <h1 class="p-l-0 contract-forum-view-button">Create folder
                    <?php echo $this->Html->link($this->Html->tag('span', '', array('class' => 'fa fa-arrow-left')), array('controller' => 'documents', 'action' => 'index'), array('title' => 'Back to Documents', 'escape' => false)); ?>
                </h1>
            </div>
        </div>

        <div class="ionic-table">
            
            <?php
            echo $this->Form->create('Document', array(
                'name' => 'ProjectForm',
                'enctype' => 'multipart/form-data'
            ));
            ?>

            <div class="ionic-table edit-form">
                <div class="ionic-form">
                    <label for="folder_project_id">Name*</label>
                    <div class="col-lg-10">
                        <?php
                        echo $this->Form->input('Name', array(
                            'id' => 'document_foldername',
                            'type' => 'text',
                            'class' => 'input_txt',
                            'placeholder' => 'Enter folder name',
                            'label' => false
                                )
                        );
                        ?>
                    </div>
                </div>

                <div class="ionic-form">
                    <label for="folder_project_id">Project*</label>
                    <div class="col-lg-10">
                        <?php
                        echo $this->form->input('ProjectID', array(
                            'empty' => 'No Project',
                            'id' => 'folder_project_id',
                            'type' => 'select',
                            'name' => 'data[Document][ProjectID]',
                            'label' => false,
                            'options' => $projectData
                                )
                        );
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="edit-action checkbox-btn"> 
            <?php echo $this->Form->input('CreatedDate', array('type' => 'hidden', 'value' => date('Y-m-d H:i:s'))); ?>
            <?php echo $this->Form->input('ChangedDate', array('type' => 'hidden', 'value' => date('Y-m-d H:i:s'))); ?>
            <?php echo $this->Form->submit('Submit', array('class' => 'edit')); ?>
            <?php echo $this->Html->link('Cancel', array('controller' => 'documents', 'action' => 'index'), array('class' => 'logout', 'escape' => false)); ?>
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
        $("#DocumentNewFolderForm").validate();

        $("#document_foldername").rules("add", {
            required: true,
            messages: {
                required: "Please enter folder name"
            }
        });

        $("#folder_project_id").rules("add", {
            required: true,
            messages: {
                required: "Please choose a project"
            }
        });


    });

    /*********************  End Editing By T:307 ***********************/
</script>



