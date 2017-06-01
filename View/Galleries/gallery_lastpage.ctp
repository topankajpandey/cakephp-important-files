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
		<div class="PageTitle">Write Captions (3/3)</div>
	   
		<div class="namesection">Gallery: 
		<?php  echo $photogalleryname['Gallery']['Name']; ?>
		</div>
		
		<div class="msg">Please enter captions for the uploaded photos (optional)</div>
		
	<?php echo $this->Form->create('Galleries',array('name'=>'AddCaptionsForm', 'id'=>"AddCaptionsForm",'url'=>'/gallery_lastpage')); ?>
	
	 
	    
                <div class="uploaded-image">
       <img width="108" src="http://dev414.trigma.us/projectengineer/app/webroot/uploads/files/<?php echo $photoname['GalleryPhoto']['FileName']; ?>">
                </div>
                 <div class="ionic-form">
					<?php echo $this->Form->input('Caption',array('id'=>'caption','type'=>'textarea','class'=>'input_txt','placeholder'=>'','label'=>'Caption:')); ?>					
				</div>
            
        <div class="finished">
               
                <input type="submit" value="Finished">
        </div>
		
		
        <?php echo $this->Form->end();?>
		
			
		
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
