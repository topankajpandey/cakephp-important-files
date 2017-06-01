<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">

        <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                <h1 class="p-l-0 contract-forum-view-button">Add Gallery Folder
                    <?php echo $this->Html->link($this->Html->tag('span', '', array('class' => 'fa fa-plus', 'title' => 'Add Gallery Folder')), array('controller' => 'galleries', 'action' => 'add_gallery'), array('escape' => false)); ?>
                </h1>
            </div>
        </div>


        <?php if ($this->Session->flash('success_message')): ?>
            <div class="heading-tag">
                <div class="edit-action">
                    <div class="alert alert-success fade in alert-dismissable" style="margin-top:18px;">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
                        <?php echo $this->Session->flash('success_message'); ?>
                    </div>
                </div>
            </div>	
        <?php endif; ?>
        <?php //$this->Custom->clearProjectSortLink(); ?>



        <div class="ionic-frame">
            <ul class="headlines">
                <!--li><?php
                /* echo $this->Form->create('ProjectForm', array(
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
                  echo $this->Form->end(); */
                ?></li-->
                <?php
                $memberId = $this->Session->read('Auth.User.MemberID');
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

                                                        <?php
                                                        if (empty($gallery['Forum'])) {
                                                            echo $this->Html->link('<i title="Create Forum" class="glyphicon glyphicon-plus-sign"></i>', array('controller' => 'galleries', 'action' => 'add_forum', $gallery['GalleryID'], $project['Project']['ProjectID']), array('escape' => false));
                                                        } else {
                                                            $thread = $gallery['Forum'][0]['Thread'];
                                                            echo $this->Html->link('<i title="View Forum" class="fa fa-eye"></i>', array('controller' => 'galleries', 'action' => 'view_forum', $thread), array('escape' => false));
                                                        }
                                                        ?>


                                                        <?php echo $this->Html->link('<i title="View Photo" class="glyphicon glyphicon-zoom-in"></i>', 'javascript:;', array('onclick' => "view_gallery($gallery[GalleryID])", 'id' => $gallery['GalleryID'], 'title' => 'View Photo', 'escape' => false)); ?>
                                                        <?php echo $this->Html->link('<i title="Delete" class="fa fa-remove"></i>', array('controller' => 'galleries', 'action' => 'delete_gallery', $gallery['GalleryID']), array('id' => $gallery['GalleryID'], 'confirm' => 'Are you sure you want to delete?', 'title' => 'Delete', 'escape' => false)); ?>
                                                        <?php echo $this->Html->link('<i title="Edit" class="fa fa-pencil-square-o"></i>', array('controller' => 'galleries', 'action' => 'edit_gallery', base64_encode($gallery['GalleryID'])), array('id' => $gallery['GalleryID'], 'title' => 'Edit', 'escape' => false)); ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>


                                        <div class="modal fade" id="view_gallery_photo_<?php echo $gallery['GalleryID']; ?>" role="dialog">
                                            <div class="modal-dialog">

                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close close_modal" data-dismiss="modal">&times;</button>
                                                        <h4 class="modal-title">View Photo</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <?php
                                                            $full_url = $this->webroot . 'galleries/user_' . $memberId . '/gallery_' . $gallery['GalleryID'] . '/';
                                                            $full_thumb_url = $this->webroot . 'galleries/user_' . $memberId . '/gallery_' . $gallery['GalleryID'] . '/thumb/thumb_';
                                                            if (!empty($gallery['Photo'])) {
                                                                foreach ($gallery['Photo'] as $gal) {
                                                                    $full_url_with_name = $full_url . $gal['FileName'];
                                                                    $thumb_url_with_name = $full_thumb_url . $gal['FileName'];
                                                                    echo '<div class = "col-lg-3 col-sm-4 col-xs-6">
                                                                        <a href="' . $full_url_with_name . '" class="thumbnail" title= "Listing Gallery Photo For <strong>' . ucfirst($gallery['Name']) . '</strong>">
                                                                        <img src = "' . $thumb_url_with_name . '" alt = "' . $thumb_url_with_name . '">
                                                                        </a>
                                                                        </div>';
                                                                }
                                                            } else {
                                                                echo '<div class = "col-lg-8 col-sm-4 col-xs-6">There is no photo in the gallery...</div>';
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><?php
                                    }
                                } else {
                                    echo '<div class="headline-review"><table><tr><td colspan="4" align="center">No gallery available in this project...</td></tr></table></div>';
                                }
                                ?>
                            </div> 
                        </li><?php
                    }
                } else {
                    echo '<li>';
                    echo '<div class="alert alert-danger">No project found for gallery.</div>';
                    echo '</li>';
                }
                ?>
            </ul>
        </div>  
    </div>  
</div>



<link href = '<?php echo $this->webroot; ?>assets/global/plugins/viewbox/css/viewbox.css' rel = 'stylesheet'/>
<link href = '<?php echo $this->webroot; ?>css/custom.css' rel = 'stylesheet'/>
<link href = '<?php echo $this->webroot; ?>css/datepicker/bootstrap.min.css' rel = 'stylesheet' media = 'screen' />
<style>.headlines{width:100%!important;}</style>

<script src="<?php echo $this->webroot; ?>assets/global/plugins/viewbox/js/run_prettify.js"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/viewbox/js/jquery.viewbox.min.js"></script>

<script type = "text/javascript">
    function view_gallery(gallery_id) {
        $("#view_gallery_photo_" + gallery_id).modal('show');
    }

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

    $(function () {
        $('.thumbnail').viewbox();
    });

</script> 

