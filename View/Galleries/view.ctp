<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">
        <div class="ionic-frame">
            <div class="heading-tag">
                <div class="edit-action">
                    <?php if ($this->Session->flash('success_message')): ?>
                        <div class="alert alert-success fade in alert-dismissable" style="margin-top:18px;">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
                            <?php echo $this->Session->flash('success_message'); ?>
                        </div>
                    <?php endif; ?>
                    <?php echo $this->Html->link('Add Photo Gallery', array('controller' => 'galleries', 'action' => 'add_gallery'), array('class' => 'edit documents')); ?>
                    <?php $this->Custom->clearProjectSortLink(); ?>
                </div>
            </div>	


            <ul class="headlines">
                <li><?php
                    echo $this->Form->create('ProjectForm', array(
                        'name' => 'ProjectForm',
                        'enctype' => 'multipart/form-data'
                    ));
                    echo $this->form->input('', array(
                        'empty' => 'Sort By Project',
                        'type' => 'select',
                        'name' => 'project_id',
                        'div' => false,
                        'class' => 'form-control',
                        'onchange' => 'sort_by_project()',
                        'label' => FALSE,
                        'options' => $projectList
                            )
                    );
                    echo $this->Form->end();
                    ?></li>
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
                                if (!empty($project['Gallery'])) {
                                    foreach ($project['Gallery'] as $key => $gallery) {
                                        ?><div class="headline-review">

                                            <table>
                                                <tr>
                                                    <td>
                                                        <span class="glyphicon glyphicon-picture"></span>
                                                        &nbsp;<?php echo $gallery['Name']; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $this->Html->link('<i title="Delete" class="fa fa-remove"></i>', array('controller'=>'galleries', 'action'=> 'delete_gallery', $gallery['GalleryID']), array('id' => $gallery['GalleryID'], 'confirm'=>'Are you sure you want to delete?', 'title' => 'Delete', 'escape' => false)); ?>
                                                        <?php echo $this->Html->link('<i title="Edit" class="fa fa-pencil-square-o"></i>', array('controller'=>'galleries', 'action'=> 'edit_gallery', $gallery['GalleryID']), array('id' => $gallery['GalleryID'], 'title' => 'Edit', 'escape' => false)); ?>
                                                    </td>
                                                </tr>
                                            </table>

                                        </div><?php
                                    }
                                } else {
                                    echo '<div class="headline-review"><table><tr><td colspan="4" align="center">No gallery available in this project...</td></tr></table></div>';
                                }
                                ?>
                            </div> 
                        </li><?php
                    }
                }
                ?>
            </ul>
        </div>  
    </div>  
</div>


<link href='<?php echo $this->webroot; ?>css/custom.css' rel='stylesheet'/> 
<link href='<?php echo $this->webroot; ?>css/datepicker/bootstrap.min.css' rel='stylesheet' media='screen' />

<style>.headlines{width:100%!important;}</style>
<script type="text/javascript">

    function ConfirmDelete(GalleryID)
    {
        if (confirm("Are you sure you want to delete this Gallery?"))
        {
            window.location.href = "<?php echo $this->webroot; ?>galleries/delete_gallery/" + GalleryID;

        }
    }

    
    function sort_by_project() {
        $("#ProjectFormIndexForm").submit();
    }

</script> 

