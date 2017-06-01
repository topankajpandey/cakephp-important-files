<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div id="ajax_processing"></div>
    <div class="activity-right">

        <div class="heading-tag">
            <div class="edit-action">
                <?php if ($this->Session->flash('success_message')): ?>
                    <div class="alert alert-success fade in alert-dismissable" style="margin-top:18px;">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
                        <?php echo $this->Session->flash('success_message'); ?>
                    </div>
                <?php endif; ?>

                <?php echo $this->Html->link('Upload New Document', array('controller' => 'documents', 'action' => 'NewDocument'), array('class' => 'edit documents')); ?>
                <?php echo $this->Html->link('New Folder', array('controller' => 'documents', 'action' => 'NewFolder'), array('class' => 'edit documents')); ?>

                <?php $this->Custom->clearProjectSortLink(); ?>

            </div>
        </div>	

        <div class="ionic-frame"> 
            <ul class="headlines <?php
            if (empty($projectData)) {
                echo 'border-none';
            }
            ?>">
                <!--li><?php
                /* echo $this->Form->create('ProjectForm', array(
                  'name' => 'ProjectForm',
                  'enctype' => 'multipart/form-data'
                  ));
                  echo $this->form->input('', array(
                  'empty' => 'Filter by Project',
                  'type' => 'select',
                  'name' => 'project_id',
                  'div' => false,
                  'class' => 'form-control',
                  'onchange' => 'sort_by_project()',
                  'label' => FALSE,
                  'options' => $projectList
                  )
                  );
                  echo $this->Form->end(); */
                ?></li-->
                <?php
                if (!empty($projectData)) {
                    foreach ($projectData as $project) {
                        ?><li>
                            <div class="text">
                                <p>
                                    <strong>Project: </strong>
                                    <?php echo ucfirst($project['Project']['Name']); ?>
                                </p>
                                <?php
                                if (!empty($project['Folders'])) {
                                    foreach ($project['Folders'] as $folder) {
                                        ?><div class="headline-review" id="toggle_<?php echo $folder['FolderID']; ?>">

                                            <table>
                                                <tr>
                                                    <td>
                                                        <a title="plus" class="minified" href="javascript:void(0);" onclick="toggleDocument(<?php echo $folder['FolderID']; ?>)"><img title="plus" class="minified_img_<?php echo $folder['FolderID']; ?>" src="<?php echo $this->webroot; ?>img/plus.png"></a>
                                                        <span><img  src="<?php echo $this->webroot; ?>img/Folder_small.png"><?php echo $folder['Name']; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if (empty($folder['Forum'])) {
                                                            echo $this->Html->link('<i title="Create Forum" class="glyphicon glyphicon-plus-sign"></i>', array('controller' => 'documents', 'action' => 'add_forum', $folder['FolderID'], $project['Project']['ProjectID']), array('escape' => false));
                                                        }else{
                                                           $thread = $folder['Forum'][0]['Thread'];
                                                           echo $this->Html->link('<i title="View Forum" class="fa fa-eye"></i>', array('controller' => 'documents', 'action' => 'view_forum', $thread), array('escape' => false)); 
                                                        }
                                                        ?>

                                                        <?php echo $this->Html->link('<i title="Delete" class="fa fa-remove"></i>', array('controller' => 'documents', 'action' => 'delete', $folder['FolderID']), array('escape' => false, 'confirm' => 'Are you sure you want to delete?'));
                                                        ?>
                                                        <?php echo $this->Html->link('<i title="Rename" class="fa fa-pencil-square-o"></i>', array('controller' => 'documents', 'action' => 'rename', $folder['FolderID']), array('escape' => false)); ?>
                                                    </td>
                                                </tr>
                                            </table>
                                            <div class="colapse_process_table" id="colapse_table_<?php echo $folder['FolderID']; ?>">
                                                <table>
                                                    <?php
                                                    if (!empty($folder['Documents'])) {
                                                        foreach ($folder['Documents'] as $document) {
                                                            ?>
                                                            <tr class="document_file_<?php echo $document['DocumentID']; ?>">
                                                                <td style="width: 30px;"><img width="30" src="<?php echo $this->webroot; ?>img/icon_Document.png"></td>
                                                                <td style="width: 275px;"><?php echo ($document['ServerFileName']) ? $document['ServerFileName'] : ''; ?></td>
                                                                <td style="width: 207px;"><?php echo ($document['Description']) ? $document['Description'] : ''; ?></td>
                                                                <td>
                                                                    <a href="javascript:void(0);" onClick="delete_files(<?php echo $document['DocumentID']; ?>, <?php echo $document['ProjectID']; ?>)"><i title="Remove" class="fa fa-remove"></i></a>
                                                                    <a href="javascript:void(0);" onClick="model_email(<?php echo $document['DocumentID']; ?>)"><i title="Share" class="fa fa-share"></i></a>
                                                                    <?php echo $this->Html->link('<i title="Download" class="fa fa-download"></i>', array('controller' => 'documents', 'action' => 'download_files', $document['DocumentID']), array('title' => 'Download', 'escape' => false)); ?>
                                                                </td>
                                                            </tr><?php
                                                        }
                                                    } else {
                                                        echo '<tr><td align="center" colspan="4">There are no documents in this folder</td></tr>';
                                                    }
                                                    ?> 
                                                </table> 
                                            </div>
                                        </div><?php
                                    }
                                }
                                ?>
                            </div> 
                        </li><?php
                    }
                } else {
                    echo '<li><center><strong>No data avialable.</strong></center></li>';
                }
                ?>
            </ul>
        </div>  
    </div>  
</div>


<link href='<?php echo $this->webroot; ?>css/custom.css' rel='stylesheet'/> 
<link href='<?php echo $this->webroot; ?>css/datepicker/bootstrap.min.css' rel='stylesheet' media='screen' />
<script src='<?php echo $this->webroot; ?>js/datepicker/bootstrap.min.js'></script>

<div class="modal fade" id="share_document_by_email" role="dialog">
    <div class="modal-dialog" role="document">
        <?php
        echo $this->Form->create('Projects', array(
            'name' => 'Document',
            'enctype' => 'multipart/form-data'
        ));
        ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    Share Document <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </h5>
            </div>
            <div class="modal-body">

                <div class="form-group" id="form-group-process">
                    <label for="recipient-name" class="form-control-label">Email</label>
                    <?php
                    echo $this->Form->input('email', array(
                        'id' => 'email',
                        'type' => 'text',
                        'name' => 'email',
                        'class' => 'form-control email_class',
                        'placeholder' => 'Enter email',
                        'required' => true,
                        'label' => false
                            )
                    );
                    ?>
                    <?php
                    echo $this->Form->input('action', array(
                        'id' => 'action',
                        'type' => 'hidden',
                        'name' => 'action',
                        'value' => 'sendDocument'
                            )
                    );
                    ?>
                    <label id="email-error"></label>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" onclick="send_email()" class="btn btn-primary">Send</a>
            </div>
            <div id="hidden"></div>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
</div>
<style>.headlines{width:100%!important;}</style>
<script type="text/javascript">

    function send_email() {
        var formdata = $("#ProjectsIndexForm").serialize();
        $.ajax({
            type: "post",
            beforeSend: function () {
                $("#email-error").html('Processing....');
            },
            url: "documents/ajaxRequest",
            data: formdata,
            success: function (res) {
                var json = $.parseJSON(res);
                if (json.status_code == 200) {
                    $("#email-error").html('');
                    alert('Document send successfully');
                    $('#share_document_by_email').modal('hide');
                } else {
                    $("#email-error").html(json.response);
                }
            }
        });
    }

    function delete_files(id, project_id) {
        if (confirm('Are you sure to want to delete?')) {
            $.ajax({
                type: 'POST',
                beforeSend: function () {
                    $("#ajax_processing").html('<?php echo $this->Common->ajax_loader(); ?>');
                    $(".colapse_process_table .document_file_" + id).css('background', 'red');
                },
                url: '<?php echo Router::url(array('controller' => 'documents', 'action' => 'delete_document_data')); ?>/',
                data: {id: id, project_id: project_id},
                success: function (result) {
                    $("#ajax_processing").html('<?php echo $this->Common->ajax_loader('false'); ?>');
                    var json = $.parseJSON(result);
                    if (json.status_code == 200) {
                        $(".colapse_process_table .document_file_" + id).slideUp(300, function () {
                            $(".colapse_process_table .document_file_" + id).remove();
                        });
                    } else {
                        alert(json.response);
                    }
                },
                error: function (error) {
                    alert(error);
                }
            });
        }
    }

    function model_email(id) {
        $('.email_class').val("");
        $('#share_document_by_email').modal('show');
        $('#hidden').html('<input type="hidden" value="' + id + '" name="id" name="DocumentID">');
    }

    function toggleDocument(id) {
        var title = $(".minified_img_" + id).attr('title');
        if (title == 'plus') {
            $(".minified_img_" + id).attr('title', 'minus');
            $(".minified_img_" + id).attr('src', '<?php echo $this->webroot; ?>img/minus.png');
        } else {
            $(".minified_img_" + id).attr('title', 'plus');
            $(".minified_img_" + id).attr('src', '<?php echo $this->webroot; ?>img/plus.png');
        }
        $("#colapse_table_" + id).slideToggle();
    }

    function sort_by_project() {
        $("#ProjectFormIndexForm").submit();
    }


</script> 

