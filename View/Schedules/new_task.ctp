<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">
        
        <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                <h1 class="p-l-0 contract-forum-view-button">Create New Task
                    <?php echo $this->Html->link($this->Html->tag('span', '', array('class' => 'fa fa-arrow-left')), '/sc_home', array('title' => 'Back to Schedules', 'escape' => false)); ?>
                </h1>
            </div>
        </div>
        
        <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                
                <?php
                echo $this->Form->create('Task', array(
                    'name' => 'newTaskForm',
                    'id' => "newTaskForm",
                    'url' => '/new_task'));
                ?>

                <div class="ionic-table edit-form">

                    <div class="row">
                        <div class="col-xs-6 form-group">
                            <label for="folder_project_id">Task Name*</label>
                            <?php
                            echo $this->form->input('Task.Name', array(
                                'id' => 'task_name',
                                'type' => 'text',
                                'label' => false,
                                'class' => 'form-control',
                                'required' => false,
                                'placeholder' => 'Enter task name'
                                    )
                            );
                            ?>
                        </div>

                        <div class="col-xs-6 form-group">
                            <label for="folder_project_id">Project</label>
                            <?php
                            echo $this->form->input('Task.ProjectID', array(
                                'id' => 'ProjectID',
                                'type' => 'select',
                                'label' => false,
                                'class' => 'form-control',
                                'required' => false,
                                'options' => $projectList,
                                'empty' => 'Choose Option'
                                    )
                            );
                            ?>

                        </div>


                        <div class="col-xs-6 form-group">
                            <label for="folder_project_id">Start Date*</label>
                            <?php
                            echo $this->form->input('Task.StartDate', array(
                                'id' => 'document_start_date',
                                'type' => 'text',
                                'label' => false,
                                'class' => 'form-control',
                                'required' => false,
                                'placeholder' => 'Select start date'
                                    )
                            );
                            ?>
                        </div>

                        <div class="col-xs-6 form-group">
                            <label for="folder_project_id">End Date*</label>
                            <?php
                            echo $this->form->input('Task.EndDate', array(
                                'id' => 'document_end_date',
                                'type' => 'text',
                                'class' => 'form-control',
                                'label' => false,
                                'required' => false,
                                'placeholder' => 'Select end date'
                                    )
                            );
                            ?>
                        </div>

                        <div class="col-xs-6 form-group">
                            <label for="folder_project_id">Assigned To</label>
                            <select name="data[Task][AssignedTo]" class="form-control">
                                <?php
                                if (count($membersLists) > 0) {
                                    foreach ($membersLists as $member) {
                                        ?>
                                        <option value="<?php echo $member['Member']['MemberID']; ?>"><?php echo $member['Member']['username']; ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <?php
                        /*echo $this->Form->input('Task.MilestoneID', array(
                            'type' => 'hidden',
                            'value' => $MilestoneID,
                                )
                        );*/
                        ?>

                        <div class="edit-action checkbox-btn possition-input"><hr> 
                            <?php echo $this->Form->submit('Submit', array('class' => 'edit')); ?>
                            <?php echo $this->Html->link('Cancel', '/sc_home', array('class' => 'logout', 'escape' => false)); ?>
                        </div>
                    </div>
                </div>
                <?php echo $this->Form->end(); ?>
            </div>

        </div>
    </div>
</div>
<link href='<?php echo $this->webroot; ?>css/custom.css' rel='stylesheet'/> 
<link href='<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css' rel='stylesheet'/> 
<link href='<?php echo $this->webroot; ?>assets/global/plugins/jquery-ui/jquery-ui.min.css' rel='stylesheet'/> 
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-ui/jquery-ui.min.js"></script>
<script>
    $(document).ready(function () {
        $("#document_start_date").attr('readOnly', 'true');
        $("#document_end_date").attr('readOnly', 'true');
        $("#document_start_date").datepicker({dateFormat: 'yy-mm-dd', minDate: 0});
        $("#document_end_date").datepicker({dateFormat: 'yy-mm-dd', minDate: 0});
    });
</script>
