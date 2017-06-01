<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">
        
        <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                <h1 class="p-l-0 contract-forum-view-button">Showing the thread list
                    <?php echo $this->Html->link($this->Html->tag('span', '', array('class' => 'fa fa-arrow-left', 'title' => 'Back to Gallery')), '/gallery', array('escape' => false)); ?>
                </h1>
            </div>
        </div>
        
        <div class="ionic-table">
            <div class="before_thread">
                <?php if ($this->Session->read('Message.flash')) { ?>
                     <div class="alert alert-info">
                        <p><?php echo $this->Session->flash(); ?></p>
                    </div>
                <?php } ?>

                
            </div>
            <hr>
            <?php        
            if (!empty($forumData))
                foreach ($forumData as $thread) {
                    if ($thread['Forum']['Level'] == 0) {
                        $margin_value = $thread['Forum']['Level'];
                    } else {
                        $margin_value = ($thread['Forum']['Level'] * 10 + 10);
                    }
                    ?><div class="forum-thread" id="thread_<?php echo $thread['Forum']['ForumPostID']; ?>" style="margin-left:<?php echo $margin_value; ?>px">
                        <div class="heading_area">
                            <h4><?php echo $thread['Forum']['Subject']; ?></h4>
                            <author>By <?php echo $thread['User']['FirstName']; ?> <?php echo $thread['User']['LastName']; ?></author>
                            <span>
                                <i class="fa fa-clock-o fa-3" aria-hidden="true" style="font-size: 17px;"></i>
                                <?php echo date('F j, Y H:i', strtotime($thread['Forum']['PostedDate'])); ?> 
                            </span>
                        </div>
                        <p><?php echo $thread['Forum']['Message']; ?> </p>
                        <div class="forum_action_buttons">
                            <?php echo $this->Html->link('Reply', array('controller' => 'galleries', 'action' => 'reply_forum', $thread['Forum']['ForumPostID'], $forum_thread), array('title' => 'Reply', 'escape' => false)); ?>
                        </div>
                        <?php if ($thread['Forum']['Level'] > 0) : ?>
                            <div class="forum_action_buttons">
                                <?php echo $this->Html->link('Delete', 'javascript:;', array('id' => $thread['Forum']['ForumPostID'], 'class' => 'delete_forum_comment', 'title' => 'Delete', 'escape' => false)); ?>
                            </div>
                        <?php endif; ?>
                    </div><?php
                }
            ?>
        </div>
    </div>
</div>
<link href='<?php echo $this->webroot; ?>css/custom.css' rel='stylesheet'/> 
<script>
    $(document).ready(function () {
        $(".delete_forum_comment").click(function () {
            if (confirm('Are you sure to delete this comment?')) {
                var forum_id = $(this).attr('id');
                $.ajax({
                    type: 'GET',
                    beforeSend: function () {
                        $("#thread_" + forum_id).css('background', '#fb6c6c');
                        $("#thread_" + forum_id).animate(false, 300);
                    },
                    url: '<?php echo Router::url('/', true); ?>forums/delete/' + forum_id,
                    success: function (result) {
                        var json = $.parseJSON(result);
                        if (json.status_code == 200) {
                            $("#thread_" + forum_id).slideUp(300, function () {
                                $("#thread_" + forum_id).remove();
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
        })
    });
</script>