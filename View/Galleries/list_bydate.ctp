<div class="activity-main">
	<?php echo $this->element('left_sidebar'); ?>
<div class="button workplace capitalize" style="display:inline; float:left; margin:10px 0px 0px 25px;"><a href="http://dev414.trigma.us/projectengineer/galleries/new_gallery"><h1>New Photo Gallery</h1></a></div>
	<div class="activity-right">
	<h1>Most Recent</h1>
	<?php
	
	foreach($gallerylisting as $listing){
		// print_r($listing); 
		// exit;
        //echo $projectList['Project']['Gallery'][0]['GalleryID'];
		?>
		
			<div class="">
			<div class="">
			<a href=""><?php echo $listing['Gallery']['Name']; ?></a>
			</div>
			<div class="">by  </div>
			<div class=""><?php echo $listing['Gallery']['CreatedBy']; ?></div>
			<div onclick="ConfirmDelete(<?php echo $listing['Gallery']['GalleryID']; ?>);" class="Photos_delete">
			<a href="#">Delete</a></div><div class="Photos_edit">
			<a href="http://dev414.trigma.us/projectengineer/galleries/edit_gallery/<?php echo $listing['Gallery']['GalleryID']; ?>">Edit</a>
			</div>
		
			</div>
		
<?php
	} 
	
		?>
		
	
					
						
					
				</div>
</div>
<script>
$( document ).ready(function() {
   $("li.greycolor:even").addClass("grey");
});

function ConfirmDelete(GalleryID)
{
if (confirm("Are you sure you want to delete this Gallery?"))
	{
	window.location.href = "http://dev414.trigma.us/projectengineer/galleries/delete_gallery/"+GalleryID;
	
	}
}
</script>
