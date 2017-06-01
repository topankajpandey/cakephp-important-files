<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">

        <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                <h1 class="p-l-0">Create New Task</h1>
                <?php
                echo $this->Form->create('Schedule', array(
                    'id' => "newTaskForm",
                    'method' => 'Post',
                    'url' => array('controller' => 'schedules', 'action' => 'edit_task', $MilestoneID))
                );
                ?>

                <div class="ionic-table edit-form">

                    <div class="row">
                        <div class="col-xs-6 form-group">
                            <label for="folder_project_id">Task Name*</label>
                            <?php
                            echo $this->form->input('Schedule.Name', array(
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
                            <select name="data[Schedule][ProjectID]" class="form-control">
                                <?php
                                if (count($projectLists) > 0) {
                                    foreach ($projectLists as $project) {
                                        ?><option value="<?php echo $project['Project']['ProjectID']; ?>" <?php
                                        if ($this->data['Schedule']['ProjectID'] == $project['Project']['ProjectID']) {
                                            echo "selected";
                                        }
                                        ?>><?php echo $project['Project']['Name']; ?></option><?php
                                            }
                                        }
                                        ?>
                            </select>
                        </div>

                        <div class="col-xs-6 form-group">
                            <label for="folder_project_id">Start Date*</label>
                            <?php
                            echo $this->form->input('Schedule.StartDate', array(
                                'id' => 'document_start_date',
                                'type' => 'text',
                                'label' => false,
                                'value' => date('Y-m-d', strtotime($this->data['Schedule']['StartDate'])),
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
                            echo $this->form->input('Schedule.EndDate', array(
                                'id' => 'document_end_date',
                                'type' => 'text',
                                'class' => 'form-control',
                                'value' => date('Y-m-d', strtotime($this->data['Schedule']['EndDate'])),
                                'label' => false,
                                'required' => false,
                                'placeholder' => 'Select end date'
                                    )
                            );
                            ?>
                        </div>

                        <div class="col-xs-6 form-group">
                            <label for="folder_project_id">Assigned To</label>
                            <select name="data[Schedule][AssignedTo]" class="form-control">
                                <?php
                                if (count($membersLists) > 0) {
                                    foreach ($membersLists as $member) {
                                        ?>
                                        <option value="<?php echo $member['Member']['MemberID']; ?>" <?php
                                        if ($member['Member']['MemberID'] == $this->data['Schedule']['AssignedTo']) {
                                            echo "selected";
                                        }
                                        ?>><?php echo $member['Member']['username']; ?></option>
                                                <?php
                                            }
                                        }
                                        ?>
                            </select>
                        </div>
                        
                        <div class="col-xs-6 form-group">
                            <label for="folder_project_id">Status*</label>
                            <?php
                            echo $this->form->input('Schedule.Status', array(
                                'id' => 'document_status',
                                'type' => 'select',
                                'class' => 'form-control',
                                'label' => false,
                                'required' => false,
                                'options' => [0 => 'Desable', 1 => 'Enable']
                                    )
                            );
                            ?>
                        </div>

                        <?php
                        echo $this->Form->input('Schedule.TaskID', array(
                            'type' => 'hidden',
                                )
                        );
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
