<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">
        
         <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                <h1 class="p-l-0 contract-forum-view-button">Create folder
                    <?php echo $this->Html->link($this->Html->tag('span', '', array('class' => 'fa fa-arrow-left', 'title' => 'Back to Gallery')), array('controller' => 'galleries', 'action' => 'index'), array('escape' => false)); ?>
                </h1>
            </div>
        </div>
        
        
        <?php
        echo $this->Form->create('Gallery', array(
            'name' => 'ProjectForm',
            'enctype' => 'multipart/form-data'
        ));
        ?>

        <div class="ionic-table edit-form">
            <div class="ionic-form">
                <?php
                echo $this->Form->input('Gallery.Name', array(
                        'id' => 'Name',
                        'type' => 'text',
                        'class' => 'input_txt',
                        'placeholder' => 'Enter photo gallery name',
                        'label' => 'Gallery Name*'
                    )
                );
                ?>
            </div>

            <div class="ionic-form">
                <?php
                echo $this->form->input('Gallery.ProjectID', array(
                        'empty' => 'No Project',
                        'id' => 'folder_project_id',
                        'type' => 'select',
                        'label' => 'Project',
                        'options' => $projectData
                    )
                );
                ?>
            </div>
        </div>
        <div class="edit-action checkbox-btn"> 
            <?php echo $this->Form->input('Gallery.CreatedDate', array('type' => 'hidden', 'value' => date('Y-m-d H:i:s'))); ?>
            <?php echo $this->Form->input('Gallery.ChangedDate', array('type' => 'hidden', 'value' => date('Y-m-d H:i:s'))); ?>
            <?php echo $this->Form->submit('Submit', array('class' => 'edit')); ?>
            <?php echo $this->Html->link('Cancel', array('controller' => 'galleries', 'action' => 'index'), array('class'=> 'logout', 'escape' => false)); ?>
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
        $("#GalleryAddGalleryForm").validate();

        $("#Name").rules("add", {
            required: true,
            messages: {
                required: "Please enter gallery name"
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



