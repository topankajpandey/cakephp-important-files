<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">
        
        <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                <h1 class="p-l-0 contract-forum-view-button">Latest Activity
                    
                </h1>
            </div>
        </div>&nbsp;<br>
	
        <div class="ionic-table">
            <div class="calenderCol">
                <div id='calendar'>
                </div>
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
<link href='<?php echo $this->webroot; ?>css/calender/fullcalendar.min.css' rel='stylesheet' />
<link href='<?php echo $this->webroot; ?>css/calender/fullcalendar.print.min.css' rel='stylesheet' media='print' /> 

<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-ui.js"></script>
<script src='<?php echo $this->webroot; ?>js/calender/moment.min.js'></script>
<script src='<?php echo $this->webroot; ?>js/calender/fullcalendar.min.js'></script>

<script> 

    $(document).ready(function () {
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay,listWeek'
            },
            defaultDate: '<?php echo date('Y-m-d'); ?>',
            navLinks: true,
            weekNumbers: true,
            weekNumbersWithinDays: true,
            weekNumberCalculation: 'ISO',
            editable: true,
            eventLimit: true,
            events: <?php echo json_encode($taskLists, JSON_UNESCAPED_SLASHES); ?>
        });
    });

</script>