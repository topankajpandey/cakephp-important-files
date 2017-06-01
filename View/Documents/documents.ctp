<div class="activity-main">
  <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">
        <div class="heading-tag">
            <h1>Latest Activity </h1>
        </div>	
        <div class="ionic-frame">
            <div class="frame"><img src="/projectengineer/img/images/frame.png" alt=""></div>
            <div class="frame-setup">
                <h1>Ioninc frame work</h1>
                <span><img src="/projectengineer/img/images/clock.png" alt=""> <span class="clock-span">10 minutes ago</span></span>
                <h6>Wray Hodgson has posted in the project forum</h6>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent malesuada porttitor pretium. Sed venenatis felis quis urna mollis, sit amet aliquet dui dapibus. Suspendisse pellentesque id est eget ullamcorper. Nam sit amet luctus sem. Nullam a bibendum dolor. Proin tristique,        <a href="#">More</a>...</p>
            </div>
        </div>
    </div>
</div>

<div data-role="popup" id="myPopup">
    <div class="popUpBody">
        <div class="popHeader"><h3> Post Reply </h3>
            <a href="#" id="noBtn"><img src="<?php echo $this->webroot; ?>img/images/cross.png"/></a></div>
        <div id="tabs" class="tabbing-popup">
            <ul>
                <li><a href="#tabs-1">Reply</a></li>
                <li><a href="#tabs-2">Document</a></li>
            </ul>
            <div id="tabs-1">
                <textarea></textarea>
            </div>
            <div id="tabs-2">
                <div class="document">
                    <div class="document-header">
                        <input type="checkbox"><span>Document Name</span>
                    </div>
                    <div class="document-para"><p>There are no document attached to this post.</p></div>
                    <div class="document-action">
                        <div class="actions">
                            <a href="#" class="okay">Upload Document</a>
                            <a href="#" class="cancel">Remove Document</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="popup-anony">
            <div class="anony"><input type="checkbox"><span>Anonymous</span></div>
            <div class="actions">
                <a href="#" class="okay">Ok</a>
                <a href="#" class="cancel">Cancel</a>
            </div>
        </div>
    </div>
</div>


<!-- JS Files -->

<script type="text/javascript" src="<?php echo $this->webroot; ?>js/simpleMobileMenu.js"></script> 
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/html5.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>js/jquery.auroramenu.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<!-- <script type="text/javascript" src="js/custom.js"></script> -->
<script>
    //Menu Slide Js
    jQuery(document).ready(function ($) {
        $('.smobitrigger').smplmnu();
    });
</script>
<script>
    $(".popBtn").click(function () {
        $("#myPopup").toggle()
    });

    $("#yesBtn").click(function () {
        $("#myPopup").hide()
    });

    $("#noBtn").click(function () {
        $("#myPopup").hide()
    });
</script>
<script>
    $(function () {
        $("#tabs").tabs();
    });
</script>
<!-- Calender Css-->    
<link href='<?php echo $this->webroot; ?>css/calender/fullcalendar.min.css' rel='stylesheet' />
<link href='<?php echo $this->webroot; ?>css/calender/fullcalendar.print.min.css' rel='stylesheet' media='print' />
<!-- Calender Js-->    
<script src='<?php echo $this->webroot; ?>js/calender/moment.min.js'></script>
<script src='<?php echo $this->webroot; ?>js/calender/fullcalendar.min.js'></script>

