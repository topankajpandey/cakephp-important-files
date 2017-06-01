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
		<div class="PageTitle">Choose a Name (1 of 3)</div>
		 
		<div style="margin:50px 0px 0px 25px; text-align:left;">Please choose a name for your new photo gallery:</div>
		
	<?php echo $this->Form->create('Galleries',array('name'=>'newGalleryForm', 'id'=>"newGalleryForm",'url'=>'/new_gallery')); ?>
	<div class="ionic-form">
					<?php echo $this->Form->input('PGName',array('id'=>'pg_name','type'=>'text','class'=>'input_txt','placeholder'=>'','label'=>'Photo Gallery Name:')); ?>					
				</div>
       <div class="ionic-form">
			
                    <div class="name">Project:</div>
                    <div class="value">
                    	<select name="ProjectID">
                       <?php
				if(count($projectLists) > 0){
				 foreach($projectLists as $project){ 
				// print_r($project);
				 	 ?>
				 <option value="<?php echo $project['Project']['ProjectID']; ?>"><?php echo $project['Project']['Name']; ?></option>
			
				 <?php
				} }
				?>
                	</select>
                    </div>
        	       	
				</div>
        <div class="button-group">
                <input type="button" value="Back" onclick="javascript:history.back()">
                <input id="next" type="submit" value="Next">
        </div>
		
		
        <?php echo $this->Form->end();?>
		
		
				
		
	</div>
</div>
