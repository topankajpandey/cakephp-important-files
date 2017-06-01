<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">

        <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                <h1 class="p-l-0 contract-forum-view-button">Create New Task
                    <?php echo $this->Html->link($this->Html->tag('span', '', array('class' => 'fa fa-plus', 'title' => 'Create New Task')), 'javascript:;', array('onclick' => 'get_project()', 'escape' => false)); ?>
                </h1>
            </div>
        </div>
        <div class="ionic-table">
            <div class="calenderCol">
                <?php
                if (!empty($taskLists)) {
                    echo '<div class="gantt"></div>';
                } else {
                    echo '<div class="alert alert-danger">No task found in this project.</div>';
                }
                ?>
                <!--div id='calendar'></div-->
            </div>
        </div>

    </div>
</div>

<link href='<?php echo $this->webroot; ?>css/custom.css' rel='stylesheet'/> 
<link rel='stylesheet' href="<?php echo $this->webroot; ?>assets/global/plugins/gant-chart/css/style.css">
<script type="text/javascript" src="<?php echo $this->webroot; ?>assets/global/plugins/gant-chart/js/jquery.fn.gantt.js"></script>
<script type="text/javascript">
    function find_task_by_project(params) {
        $("#schedulesScHomeForm").submit();
    }

    function get_project() {
        window.location.href = "<?php echo $this->webroot; ?>schedules/new_task/";
    }

    $(function () {
        "use strict";
        $(".gantt").gantt({
            source: <?php echo json_encode($taskLists); ?>,
            navigate: "scroll",
            scale: "years",
            minScale: "hours",
            itemsPerPage: 10,
            useCookie: true,
            /*onItemClick: function (data) {
             console.log(data);
             },
             onAddClick: function (dt, rowId) {
             alert("Empty space clicked - add an item!");
             },
             onRender: function () {
             if (window.console && typeof console.log === "function") {
             console.log("chart rendered");
             }
             }*/
        });
    });
</script>
