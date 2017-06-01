<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">

        <div class="column" id="middle">

            <div id="GalleryName"><?php echo $gallerydetails['Gallery']['Name']; ?><br><br><a href="http://dev414.trigma.us/projectengineer/galleries/pg_home">Back to Photo Galleries</a></div>

            <div class="arrow-div">
                <div id="BigPicLeft"><img id="Arrow_Previous" style="cursor: default;" src="http://gbc.projectengineer.net/a20001/Images/ArrowBig_Previous_Inactive.png"></div>		
                <div id="BigPicRight"><img id="Arrow_Next" style="cursor: pointer;" src="http://gbc.projectengineer.net/a20001/Images/ArrowBig_Next.png"></div>

            </div>
            <?php foreach ($photodetails as $photodetail) { ?>
                <div id="BigPic">

                </div>

            <?php } ?>
			    <div id="BigPicCaption"><?php echo $photodetail['GalleryPhoto']['Caption']; ?></div>
            <?php 
                $customPhotoArr = $customPhoto = [];
                foreach ($photodetails as $photodetail) {
                    $customCaption[] = $photodetail['GalleryPhoto']['Caption'];        
                    $customPhoto[]   = $photodetail['GalleryPhoto']['FileName'];
                ?>
            
                <div id="thumbnails">
                    <ul class="photo-list">
                        <li><img height="80" width="80" style="cursor:pointer;" onclick="DisplayPhoto(0,<?php echo $photodetail['GalleryPhoto']['PhotoID']; ?>, '<?php echo $photodetail['GalleryPhoto']['FileName']; ?>', '<?php echo $photodetail['GalleryPhoto']['Caption']; ?>');" src="http://dev414.trigma.us/projectengineer/app/webroot/uploads/files/<?php echo $photodetail['GalleryPhoto']['FileName']; ?>" class="thumbnail"></li>

                    </ul>
                </div>
            <?php } 
            //echo json_encode($customCaption);
            //echo json_encode($customPhoto);
            ?>
            <div class="backto"><a href="http://dev414.trigma.us/projectengineer/galleries/pg_home">Back to Photo Galleries</a></div>

        </div>
    </div>
</div>
<style>
    #GalleryName {
        font-size: 18px;
        font-weight: bold;
        margin-left: 15px;
        padding: 20px;
        text-align: center;
        width: 550px;
    }
    #BigPicLeft {
        display: inline;
        float: left;
        margin-left: 0;
        padding-top: 0;
        text-align: right;
        width: 75px;
    }
    #BigPicRight {
        display: inline;
        float: right;
        padding-top: 0;
        width: 75px;
    }

    #BigPicCaption {
        background: #ccc none repeat scroll 0 0;
        font-size: 12px;
        height: 45px;
        margin: 0 auto;
        padding: 5px;
        text-align: left;
        width: 560px;
    }
    #thumbnails {
        margin: 0 auto;
        width: 570px;
    }
    .photo-list {
        list-style: outside none none;
        margin: 10px 0 0;
        overflow: auto;
        padding: 0;
        white-space: nowrap;
    }
    .photo-list li {
        display: inline;
        margin-right: 10px;
    }
    .photo-list img {
        border: 0 none;
    }
</style>
<script type="text/javascript">
    
    var Photo = '<?php echo json_encode($customPhoto); ?>';
    var Caption = '<?php echo json_encode($customCaption); ?>';

    var CurrentPhoto = 0;

    function PreviousOn()
    {
        document.getElementById("Arrow_Previous").style.cursor = "pointer";
        document.getElementById("Arrow_Previous").src = "http://gbc.projectengineer.net/a20001/Images/ArrowBig_Previous.png";
        document.getElementById("Arrow_Previous").onclick = DisplayPrevPhoto;
        document.getElementById("Arrow_Previous").onmouseover = PrevArrow_Hover;
        document.getElementById("Arrow_Previous").onmouseout = PrevArrow_UnHover;
    }
    function PreviousOff()
    {
        document.getElementById("Arrow_Previous").style.cursor = "default";
        document.getElementById("Arrow_Previous").src = "http://gbc.projectengineer.net/a20001/Images/ArrowBig_Previous_Inactive.png";
        document.getElementById("Arrow_Previous").onclick = "";
        document.getElementById("Arrow_Previous").onmouseover = "";
        document.getElementById("Arrow_Previous").onmouseout = "";
    }
    function NextOn()
    {
        document.getElementById("Arrow_Next").style.cursor = "pointer";
        document.getElementById("Arrow_Next").src = "http://gbc.projectengineer.net/a20001/Images/ArrowBig_Next.png";
        document.getElementById("Arrow_Next").onclick = DisplayNextPhoto;
        document.getElementById("Arrow_Next").onmouseover = NextArrow_Hover;
        document.getElementById("Arrow_Next").onmouseout = NextArrow_UnHover;
    }
    function NextOff()
    {
        document.getElementById("Arrow_Next").style.cursor = "default";
        document.getElementById("Arrow_Next").src = "http://gbc.projectengineer.net/a20001/Images/ArrowBig_Next_Inactive.png";
        document.getElementById("Arrow_Next").onclick = "";
        document.getElementById("Arrow_Next").onmouseover = "";
        document.getElementById("Arrow_Next").onmouseout = "";
    }

    function DisplayPhoto(key, PhotoID, PhotoFile, Caption)
    {
		//var Captions = JSON.parse(Caption);
		//console.log(Caption);
        document.getElementById("BigPic").innerHTML = ""; //removeChild(document.getElementById("BigPhoto"));
        newImg = document.createElement("img");
        newImg.id = "BigPhoto";
        document.getElementById("BigPic").appendChild(newImg);
// The following workaround seems to be required since the height and width properties of the img don't change when the src changes
        document.getElementById("BigPhoto").onload = function () {
            Resize(document.getElementById("BigPhoto"), 600, 400);
        }
        document.getElementById("BigPhoto").onload = function () {
            document.getElementById("BigPhoto");
        }
        document.getElementById("BigPhoto").src = "http://dev414.trigma.us/projectengineer/app/webroot/uploads/files/" + PhotoFile;
        document.getElementById("BigPhoto").style.cursor = "pointer";

        document.getElementById("BigPhoto").onclick = function () {
            window.open("DisplayPhoto.php?g=sKy5BiMlB2DwuFwd7dSe3a7rY9XBwD&amp;FileName=" + PhotoFile, "Photo");
        }

	
        document.getElementById("BigPicCaption").innerHTML = Caption;
        //    console.log(Captions[key]);
        CurrentPhoto = key;
        if (key == 0)
            PreviousOff();
        else
            PreviousOn();
        if (key == 1)
            NextOff();
        else
            NextOn();
    }

    function Resize(photo, ContWidth, ContHeight)
    {
        var AR = photo.width / photo.height;
        if (photo.width > ContWidth)
        {
            photo.width = ContWidth;
            photo.height = ContWidth / AR;
        }
        if (photo.height > ContHeight)
        {
            photo.height = ContHeight;
            photo.width = ContHeight * AR;
        }
    }

    function DisplayPrevPhoto()
    {
        CurrentPhoto -= 1;
        document.getElementById("BigPhoto").src = "http://dev414.trigma.us/projectengineer/app/webroot/uploads/files/" + Photos[CurrentPhoto];
        document.getElementById("BigPicCaption").innerHTML = Captions[CurrentPhoto];
        document.getElementById("BigPhoto").onclick = function () {
            window.open("DisplayPhoto.php?g=7aB8z8sMEvLe2DwuFwd7dSe3a2IQ&amp;FileName=" + Photos[CurrentPhoto], "Photo");
        }
        if (CurrentPhoto == 0)
            PreviousOff();
        else
            PreviousOn();
        if (CurrentPhoto == 1)
            NextOff();
        else
            NextOn();
    }

    function DisplayNextPhoto()
    {
        CurrentPhoto += 1;
        document.getElementById("BigPhoto").src = "http://dev414.trigma.us/projectengineer/app/webroot/uploads/files/" + Photos[CurrentPhoto];
        document.getElementById("BigPicCaption").innerHTML = Captions[CurrentPhoto];
        document.getElementById("BigPhoto").onclick = function () {
            window.open("DisplayPhoto.php?g=5jy79dPCiae2DwuFwd7dSe3a2Gq&amp;FileName=" + Photos[CurrentPhoto], "Photo");
        }
        if (CurrentPhoto == 0)
            PreviousOff();
        else
            PreviousOn();
        if (CurrentPhoto == 1)
            NextOff();
        else
            NextOn();
    }

    function PrevArrow_Hover()
    {
        document.getElementById("Arrow_Previous").src = "http://gbc.projectengineer.net/a20001/Images/ArrowBig_Previous_Hover.png";
    }
    function PrevArrow_UnHover()
    {
        document.getElementById("Arrow_Previous").src = "http://gbc.projectengineer.net/a20001/Images/ArrowBig_Previous.png";
    }
    function NextArrow_Hover()
    {
        document.getElementById("Arrow_Next").src = "http://gbc.projectengineer.net/a20001/Images/ArrowBig_Next_Hover.png";
    }
    function NextArrow_UnHover()
    {
        document.getElementById("Arrow_Next").src = "http://gbc.projectengineer.net/a20001/Images/ArrowBig_Next.png";
    }

// Display first photo on page load
// $data = json_decode($json );

// $new = json_encode( $data[1] ); 
    // console.log(Photos);
	
	   var Photos = jQuery.parseJSON(Photo);
	   var Captions = jQuery.parseJSON(Caption);
     // console.log(Photos[0]);
     // console.log(Captions[0]);
    DisplayPhoto(0, 0, Photos[0], Captions[0]);			// PhotoID (argument 2) not being used

    function ConfirmDelete(TaskID, TaskName)
    {
        if (confirm("Are you sure you want to delete the Task '" + TaskName + "'?"))
        {
            window.location.href = "http://dev414.trigma.us/projectengineer/schedules/delete_task/" + TaskID;

        }
    }

    function ConfirmTaskDelete(TaskCommentID, TaskID)
    {
        if (confirm("Are you sure you want to delete this Comment?"))
        {
            window.location.href = "http://dev414.trigma.us/projectengineer/schedules/delete_taskcomment/" + TaskCommentID + "/" + TaskID;

        }
    }
</script>