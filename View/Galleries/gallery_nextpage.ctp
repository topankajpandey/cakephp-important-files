<div class="activity-main">
	<?php echo $this->element('left_sidebar'); ?>
	<div class="activity-right">
			<div class="workplace capitalize">
			<h1>Create New Gallery</h1>
			<div class="work-actions">
				<ul>
					<li><?php echo $this->Html->link($this->Html->image('images/back.png'),array('controller'=>'dashboard'), array('escape'=>false)); ?></li>
					<li><?php echo $this->Html->link($this->Html->image('images/edit.png'),'javascript:void(0);', array('escape'=>false)); ?></li>
				</ul>
			</div>
		</div>
		<div class="PageTitle">Select Images (2 0f 3)</div>
		 
		<div class="namesection">Gallery:               <?php  echo $photogalleryname['Gallery']['Name']; ?>
		   </div>
		
		<div class="msg">Please click on the button below to select images for the gallery.</div>
	
			
			 <?php echo $this->Form->create($uploadData, ['type' => 'file']); ?>
            <?php echo $this->Form->input('file', ['type' => 'file', 'class' => 'form-control']); ?>
			   <input type="button" value="Back" onclick="javascript:history.back()">
            <?php echo $this->Form->button(__('Upload File'), ['type'=>'submit', 'class' => 'form-controlbtn btn-default']); ?>
        <?php echo $this->Form->end(); ?>
		
	</div>
</div>
 
<script>

$( document ).ready(function() {
	$('#container').hide();
$("#next").click(function() {
	
   $('#container').toggle("slide", { direction: "right" }, 1000);
});

});

</script>
