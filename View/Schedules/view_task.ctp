<div class="activity-main">
    <?php echo $this->element('left_sidebar'); ?>
    <div class="activity-right">

        <div id="tabs" class="ui-tabs">
            <div id="tabs-2" class="forum-table">
                <h1 class="p-l-0 contract-forum-view-button">Task Details
                    <?php echo $this->Html->link($this->Html->tag('span', '', array('class' => 'fa fa-arrow-left')), '/sc_home', array('title' => 'Back to Schedule', 'escape' => false)); ?>
                </h1>
            </div>
        </div>

        <!--div class="workplace capitalize">
            <div class="work-actions">
                <ul>
                    <li><?php echo $this->Html->link($this->Html->image('images/back.png'), '/sc_home', array('title' => 'Back to Schedule', 'escape' => false)); ?></li>

                </ul>
            </div>
        </div-->

        <div class="ionic-table edit-form">
            <div class="ionic-form">
                <label>Task Name:</label>
                <div class="pm"><?php echo $taskDetalis['Schedule']['Name']; ?>	
                                <!--span><a href="/projectengineer/schedules/edit_task/<?php echo $taskDetalis['Schedule']['TaskID']; ?> ">Edit</a></span-->

                </div>
            </div>
            <div class="ionic-form">
                <label>Project:</label>
                <div class="pm"><?php echo $projectName['Project']['Name']; ?>				
                                <!--span><a href="/projectengineer/schedules/edit_task/<?php echo $taskDetalis['Schedule']['TaskID']; ?>">Edit</a></span-->
                </div>
            </div>
            <div class="ionic-form">
                <label>Start Date:</label>
                <div class="pm"><?php echo $startdate = date("M d ,Y ", strtotime($taskDetalis['Schedule']['StartDate'])); ?>	
                                <!--span><a href="/projectengineer/schedules/edit_task/<?php echo $taskDetalis['Schedule']['TaskID']; ?>">Edit</a></span-->
                </div>
            </div>
            <div class="ionic-form">
                <label>End Date:</label>
                <div class="pm"><?php echo $enddate = date("M d ,Y ", strtotime($taskDetalis['Schedule']['EndDate'])); ?>	
                                <!--span><a href="/projectengineer/schedules/edit_task/<?php echo $taskDetalis['Schedule']['TaskID']; ?>">Edit</a></span-->
                </div>
            </div>
            <div class="ionic-form">
                <label>Assigned By:</label>
                <div class="pm">   <?php echo $assignedby; ?></div>

            </div>
            <div class="ionic-form">
                <label>Assigned To:</label>
                <div class="pm"><?php echo $memberName['Member']['username']; ?>		
                <!--span><a href="/projectengineer/schedules/edit_task/<?php echo $taskDetalis['Schedule']['TaskID']; ?>">Edit</a></span-->
                </div>

            </div>
            <div class="ionic-form">
                <label>Status:</label>
                <div class="pm"><?php
                    if ($taskDetalis['Schedule']['Status'] == 0) {
                        echo "Not complete";
                    } else {
                        echo "Complete";
                    }
                    ?>				
                <!--span><a href="/projectengineer/schedules/edit_task/<?php echo $taskDetalis['Schedule']['TaskID']; ?>">Edit</a></span-->
                </div>

            </div>

        </div>


        <h1>Actions</h1>
        <div class="ionic-table edit-form rest-form">
            <div class="edit-action checkbox-btn rest">
                <a class="edit" href="/projectengineer/schedules/edit_task/<?php echo $taskDetalis['Schedule']['TaskID']; ?>">Edit Task</a>

                <!--a class="logout" href="/projectengineer/schedules/edit_task/<?php echo $taskDetalis['Schedule']['TaskID']; ?>">Change Status</a-->


                <a class="logout" href="#" onclick="ConfirmDelete(<?php echo $taskDetalis['Schedule']['TaskID']; ?>, '<?php echo $taskDetalis['Schedule']['Name']; ?>')">Delete Task</a>
            </div>
        </div>


        <h1>Task History</h1>
        <div class="ionic-table edit-form">
            <div id="tabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
                <div id="tabs-2" class="forum-table">
                    <table>
                        <tbody><tr class="table-heading">
                                <th>Event</th>
                                <th>Date</th>
                                <th>By</th>
                                <th></th>

                            </tr>
                            <?php foreach ($taskHistory as $history) { ?>
                                <tr>
                                    <td><?php echo $history['TaskHistory']['TaskName']; ?></td>
                                    <td><?php echo $historydate = date("Y-m-d H:m a", strtotime($history['TaskHistory']['ChangedDate'])) ?></td>
                                    <td><?php echo $assignedby; ?></td>
                                    <td></td>
                                </tr>
                            <?php } ?>	
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        <h1>Comments</h1>

        <div class="ionic-table edit-form">
            <div id="tabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
                <div id="tabs-2" class="forum-table">
                    <table>
                        <tbody>
                            <?php foreach ($taskComment as $comment) { ?>
                                <tr>
                                    <td>By <?php echo $assignedby; ?></td>
                                    <td><span> <img alt="" src="/projectengineer/img/images/clock.png"><span class="clock-span"> <?php echo $cdate = date("M d , Y", strtotime($comment['TaskComment']['Date'])); ?> </span></span></td>
                                    <td><a class="logout" onclick="ConfirmTaskDelete(<?php echo $comment['TaskComment']['TaskCommentID']; ?>,<?php echo $taskDetalis['Schedule']['TaskID']; ?>);" href="#">Delete</a></td>
                                    <td></td>
                                </tr>
                            <?php } ?>	
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <h1> Leave a Comment for this Task </h1>
        <?php echo $this->Form->create('TaskComments', array('name' => 'NewTaskCommentForm', 'id' => "NewTaskCommentForm", 'url' => '/schedules/view_task/' . $taskDetalis['Schedule']['TaskID'])); ?>
        <div id="tabs" class="tabbing-popup">
            <div class="ionic-form">			
                <div class="input textarea">
                    <textarea required="required" rows="6" cols="30" maxlength="255" class="input_txtarea" name="Comment"></textarea>
                </div>		
            </div>

            <div class="edit-action checkbox-btn">
                <div class="submit"><input type="submit" name="new_task_comment" value="Submit" class="edit"></div>				
            </div>

        </div>
        <input type="hidden" name="Action" value="new_task_comment">
        <input type="hidden" name="TaskID" value="k522vO1wuFwd7dSe3a1v">
        <input type="hidden" name="ProjectID" value="UBW6Yx5dPA2DauFwd7dSe3a32xI">
        <?php echo $this->Form->end(); ?>

        <!--div class="edit-action checkbox-btn">
            <a class="logout" href="javascript:history.back()">Back</a>	
        </div-->

    </div>
</div>
<link href='<?php echo $this->webroot; ?>css/custom.css' rel='stylesheet'/> 
<script>
    function ConfirmDelete(TaskID, TaskName)
    {
        if (confirm("Are you sure you want to delete the Task '" + TaskName + "'?"))
        {
            window.location.href = "<?php echo $this->webroot; ?>schedules/delete_task/" + TaskID;
        }
    }
    function ConfirmTaskDelete(TaskCommentID, TaskID)
    {
        if (confirm("Are you sure you want to delete this Comment?"))
        {
            window.location.href = "<?php echo $this->webroot; ?>schedules/delete_taskcomment/" + TaskCommentID + "/" + TaskID;

        }
    }
</script>