<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">


        <div class="col-md-12">

            <?php
            echo $this->Form->create('Gallery', array(
                'name' => 'ProjectForm',
                'enctype' => 'multipart/form-data',
                'id' => 'fileupload',
                'url' => '/galleries/edit_gallery/' . $gid
            ));
            ?>

            <div class="row fileupload-buttonbar">
                <div class="col-lg-12">
                    <span class="btn green fileinput-button">
                        <i class="fa fa-plus"></i>
                        <span> Add files... </span>
                        <input name="files[]" multiple="" type="file"> </span>
                    <button type="submit" class="btn blue start">
                        <i class="fa fa-upload"></i>
                        <span> Start upload </span>
                    </button>
                    
                    <!--button type="button" class="btn red delete">
                        <i class="fa fa-trash"></i>
                        <span> Delete </span>
                    </button-->

                    <?php echo $this->Html->link($this->Html->image('images/back.png'), array('controller' => 'galleries', 'action' => 'index'), array('title' => 'Back to Gallery', 'escape' => false)); ?>
                    <?php echo $this->Html->link('Use drag and drop to add multiple files in the gallery from local storage', 'javascript:;', array('escape' => false)); ?>


                    <span class="fileupload-process"> </span>
                </div>
                <div class="col-lg-11 fileupload-progress fade">
                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar progress-bar-success" style="width:0%;"> </div>
                    </div>
                    <div class="progress-extended"> &nbsp; </div>
                </div>
            </div>
            
            

            <table role="presentation" class="table table-striped clearfix">
                <tbody class="files"> </tbody>
            </table>
            <input type="hidden" value="<?php echo $gid; ?>" name="gallery_id">
            <?php echo $this->Form->end(); ?>


            <!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
            <script id="template-upload" type="text/x-tmpl"> 
                {% for (var i=0, file; file=o.files[i]; i++) { %}
                <tr class="template-upload fade">
                <td>
                <span class="preview"></span>
                </td>
                <td>
                <p class="name">{%=file.name%}</p>
                <strong class="error text-danger label label-danger"></strong>
                </td>
                <td>
                <p class="size">Processing...</p>
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                </div>
                </td>
                <td> {% if (!i && !o.options.autoUpload) { %}
                <button class="btn blue start" disabled>
                <i class="fa fa-upload"></i>
                <span>Start</span>
                </button> {% } %} {% if (!i) { %}
                <button class="btn red cancel">
                <i class="fa fa-ban"></i>
                <span>Cancel</span>
                </button> {% } %} </td>
                </tr> {% } %} 
            </script>

            <script id="template-download" type="text/x-tmpl"> {% for (var i=0, file; file=o.files[i]; i++) { %}
                <tr class="template-download fade">
                <td>
                <span class="preview"  onclick="add_caption({%=file.PhotoID%})"> {% if (file.thumbnailUrl) { %}
                <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery>
                <img src="{%=file.thumbnailUrl%}">
                </a> {% } %} </span>
                </td>
                <td>
                <p class="name"> {% if (file.url) { %}
                <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl? 'data-gallery': ''%}>{%=file.name%}</a> {% } else { %}
                <span>{%=file.name%}</span> {% } %} </p> {% if (file.error) { %}
                <div>
                <span class="label label-danger">Error</span> {%=file.error%}</div> {% } %} </td>
                <td>
                <span class="size">{%=o.formatFileSize(file.size)%}</span>
                </td>
                <td> {% if (file.deleteUrl) { %}
                <button class="btn red delete btn-sm" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}" {% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}' {% } %}>
                <i class="fa fa-trash-o"></i>
                <span>Delete</span>
                </button>
                <!--input type="checkbox" name="delete" value="1" class="toggle"--> {% } else { %}
                <button class="btn yellow cancel btn-sm">
                <i class="fa fa-ban"></i>
                <span>Cancel</span>
                </button> {% } %} </td>
                </tr> {% } %} </script>
        </div>
    </div>
</div>

<div class="modal fade" id="add_caption_to_photo" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="caption_form" action="" method="post" accept-charset="utf-8">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Add Caption <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </h5>
                </div>
                <div class="modal-body">

                    <div class="form-group" id="form-group-process">
                        <label for="recipient-name" class="form-control-label">Caption</label>
                        <?php
                        echo $this->Form->input('Photo.Caption', array(
                            'id' => 'caption',
                            'class' => 'form-control email_class',
                            'placeholder' => 'Enter caption',
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
                            'value' => 'add_caption'
                                )
                        );
                        ?>
                        <label id="caption-process-data"></label>
                        <span id="hidden_data"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="javascript:void(0)" onclick="update_caption()" class="btn btn-primary">Button</a>
                </div>
                <div id="hidden"></div>
            </div>
        </form>
    </div>
</div>

<link href='<?php echo $this->webroot; ?>css/custom.css' rel='stylesheet'/> 
<link href="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/blueimp-gallery/blueimp-gallery.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/css/jquery.fileupload-ui.css" rel="stylesheet" type="text/css" />

<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/vendor/tmpl.min.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/vendor/load-image.min.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/vendor/canvas-to-blob.min.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/blueimp-gallery/jquery.blueimp-gallery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/jquery.iframe-transport.js" type="text/javascript"></script>

<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/jquery.fileupload-process.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/jquery.fileupload-image.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/jquery.fileupload-audio.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/jquery.fileupload-video.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/jquery.fileupload-validate.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-file-upload/js/jquery.fileupload-ui.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/scripts/app.min.js" type="text/javascript"></script>

<script src='<?php echo $this->webroot; ?>assets/pages/scripts/form-fileupload.min.js' type="text/javascript"/></script>


<script type="text/javascript">
                    function add_caption(photo_id) {
                        $("#add_caption_to_photo").modal('show');
                        $("#hidden_data").html('<input type="hidden" name="data[Photo][PhotoID]" value="' + photo_id + '">')
                    }

                    function update_caption() {
                        var formdata = $("#caption_form").serialize();
                        $.ajax({
                            type: "post",
                            beforeSend: function () {
                                $("#caption-process-data").html('Processing....');
                            },
                            url: "<?php echo $this->webroot; ?>galleries/gallery_ajax",
                            data: formdata,
                            success: function (res) {
                                var json = $.parseJSON(res);
                                if (json.status_code == 200) {
                                    $("#caption-process-data").html('');
                                    $('#caption_form')[0].reset();
                                    alert(json.response);
                                    $('#add_caption_to_photo').modal('hide');
                                } else {
                                    $("#caption-process-data").html(json.response);
                                }
                            }
                        });
                    }
</script>