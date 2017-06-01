<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">
        <div class="workplace capitalize">
            <h1>Edit Gallery</h1>
            <div class="work-actions">
                <ul>
                    <li><?php echo $this->Html->link($this->Html->image('images/back.png'), array('controller' => 'dashboard'), array('escape' => false)); ?></li>

                </ul>
            </div>
        </div>

        <?php echo $this->Form->create('Galleries', array('name' => 'EditGalleryName', 'id' => "EditGalleryName", 'url' => array('controller' => 'Galleries', 'action' => 'edit_galleryname', $gid))); ?>

        <div class="ionic-form">
            <?php echo $this->Form->input('PGName', array('id' => 'pg_name', 'type' => 'text', 'class' => 'input_txt', 'placeholder' => '', 'label' => 'Photo Gallery Name:')); ?>					
        </div>

        <div class="edit-action checkbox-btn">
            <?php echo $this->Form->submit('Update', array('class' => 'edit')); ?>

            <a href="javascript:void(0)" class="logout">Cancel</a>
        </div>
        <?php echo $this->Form->end(); ?>

    </div>
</div>