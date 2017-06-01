<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">
        
        <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                <h1 class="p-l-0 contract-forum-view-button">To upload a new document, please enter the following information
                    <?php echo $this->Html->link($this->Html->tag('span', '', array('class' => 'fa fa-arrow-left')), array('controller' => 'documents', 'action' => 'index'), array('title' => 'Back to Documents', 'escape' => false)); ?>
                </h1>
            </div>
        </div>
        
        <div class="ionic-table">
            
            <?php
            echo $this->Form->create('Projects', array(
                'name' => 'ProjectForm',
                'enctype' => 'multipart/form-data'
            ));
            ?>

            <div class="ionic-table edit-form">

                <div class="ionic-form">
                    <label for="folder_project_id">File*</label>
                    <div class="col-lg-5">
                        <?php
                        echo $this->Form->input('Document.FileName', array(
                            'id' => 'document_filename',
                            'type' => 'file',
                            'class' => 'input_txt',
                            'placeholder' => '',
                            'label' => false
                                )
                        );
                        ?>
                    </div>
                </div>

                <div class="ionic-form">
                    <label for="folder_project_id">Project ID*</label>
                    <div class="col-lg-10">
                        <?php
                        echo $this->form->input('Folder.ProjectID', array(
                            'empty' => 'No Project',
                            'id' => 'folder_project_id',
                            'type' => 'select',
                            'onchange' => 'newfolder(this)',
                            'label' => false,
                            'options' => $projectData
                                )
                        );
                        ?>
                    </div>
                </div>



                <div class="ionic-form" id="folder_operation" style="display:none;"></div>

                <div class="ionic-form">
                    <label for="folder_project_id">Job Description</label>
                    <div class="col-lg-10">
                        <?php
                        echo $this->Form->input('Document.Description', array(
                            'id' => 'document_description',
                            'type' => 'textarea',
                            'class' => 'input_txtarea',
                            'placeholder' => 'Enter description....',
                            'label' => false
                                )
                        );
                        ?>
                    </div>
                </div>

                <div class="ionic-form">
                    <div class="input select">
                        <label for="folder_project_id">Select Date</label>
                        <div class="col-lg-10">
                            <div class="input text">
                                <div class="input-group date form_date col-md-5 custom_date_picker" data-date="" data-date-format="yyyy-mm-dd" data-link-field="dtp_input2" data-link-format="yyyy-mm-dd">
                                    <?php
                                    echo $this->Form->input('Document.DocumentDate', array(
                                        'id' => 'document_date',
                                        'type' => 'text',
                                        'readonly' => TRUE,
                                        'class' => 'input_txt form-control',
                                        'placeholder' => 'Choose Date',
                                        'label' => false
                                            )
                                    );
                                    ?>
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="edit-action checkbox-btn possition-input"> 
                <?php echo $this->Form->input('Folder.CreatedDate', array('type' => 'hidden', 'value' => date('Y-m-d H:i:s'))); ?>
                <?php echo $this->Form->input('Document.UploadedDate', array('type' => 'hidden', 'value' => date('Y-m-d H:i:s'))); ?>
                <?php echo $this->Form->submit('Submit', array('class' => 'edit')); ?>
                <?php echo $this->Html->link('Cancel', array('controller' => 'documents', 'action' => 'index'), array('class' => 'logout', 'escape' => false)); ?>
            </div>

            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<link href='<?php echo $this->webroot; ?>css/custom.css' rel='stylesheet'/> 
<link href='<?php echo $this->webroot; ?>css/custom.css' rel='stylesheet'/> 
<link href='<?php echo $this->webroot; ?>css/datepicker/bootstrap.min.css' rel='stylesheet' media='screen' />
<link href='<?php echo $this->webroot; ?>css/datepicker/bootstrap-datetimepicker.min.css' rel='stylesheet' media='screen' />
<script src='<?php echo $this->webroot; ?>js/datepicker/bootstrap.min.js'></script>
<script src='<?php echo $this->webroot; ?>js/datepicker/bootstrap-datetimepicker.js'></script>

<script>
    function newfolder(params) {
        if (params.value) {
            $.ajax({
                type: 'post',
                url: 'ajaxRequest',
                beforeSend: function () {
                    $("#folder_operation")
                            .css('display', 'block')
                            .html('<i class="fa fa-spinner fa-spin" style="font-size:24px"></i>');
                },
                data: 'action=getfolder&project_id=' + params.value,
                success: function (res) {
                    var json = $.parseJSON(res);
                    if (json.status_code == 200) {
                        $("#folder_operation").html(json.response);
                        $("#folder_manual").html('<label for="folder_name">Folder Name</label><div class="col-lg-10"><?php echo $this->Form->input('Folder.Name', array('id' => 'folder_name', 'required' => true, 'type' => 'text', 'class' => 'input_txt folderName', 'placeholder' => 'Enter Folder Name', 'label' => false)); ?></div>');
                    }

                }
            });
        }
    }

    function folder_selection(params) {
        if (params.value) {
            $("#folder_manual").html('');
        } else {
            $("#folder_manual").html('<label for="folder_name">Folder Name</label><div class="col-lg-10"><?php echo $this->Form->input('Folder.Name', array('id' => 'folder_name', 'required' => true, 'type' => 'text', 'class' => 'input_txt folderName', 'placeholder' => 'Enter Folder Name', 'label' => false)); ?></div>');
        }
    }

    $('.form_date').datetimepicker({
        language: 'en',
        weekStart: 1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2,
    });


    $.validator.setDefaults({
        submitHandler: function () {
            document.newProjectForm.submit();
        }
    });

    $().ready(function () {
        $("#ProjectsNewDocumentForm").validate();

        $("#document_filename").rules("add", {
            required: true,
            messages: {
                required: "Please select the document"
            }
        });

        $("#folder_project_id").rules("add", {
            required: true,
            messages: {
                required: "Please choose project"
            }
        });

        $("#folder_name").rules("add", {
            required: true,
            messages: {
                required: "Please enter folder name"
            }
        });

    });

</script>



