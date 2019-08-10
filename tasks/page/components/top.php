<div class="sticky-top" id="name">
    <div class="buttons" style="display:<?php echo $task["head"] ? "block" : "none" ?>">
        <?php $max = $task["unassigned"] == $task["local"];
        $min = $task["local"] == 0;
        newButton("showDiag('new-task')", true, "Create Subtask", true);
        echo '&nbsp&nbsp&nbsp';
        if ($min) newButton("", false, "Min hit", false, "font-size:15px; padding:4px 10px;");
        else {
            newButton("setProgress(" . $task["ID"] . ", -5, 'sidebar,name,tsk')", true, "-5", true, null, "change");
            echo " ";
            newButton("setProgress(" . $task["ID"] . ", -1, 'sidebar,name,tsk')", true, "-1", true, null, "change");
        }
        echo '&nbsp&nbsp';
        if ($max) newButton("", false, "Max hit", false, "font-size:15px; padding:4px 10px;");
        else {
            newButton("setProgress(" . $task["ID"] . ", 1, 'sidebar,name,tsk')", true, "+1", true, null, "change");
            echo " ";
            newButton("setProgress(" . $task["ID"] . ", 5, 'sidebar,name,tsk')", true, "+5", true, null, "change");
        } ?>
    </div>
    <h2 class="task-name"><?php echo $title ?></h2>
    <?php
    $head = $task["headsWanted"];
    $help = $task["helpWanted"];
    if($head || $help) {
        echo "<div class='wanted'>";
        if($head) echo "Heads";
        if ($head && $help) echo "/";
        if($help) echo "Help";
        echo " Wanted</div>";
    }

    ?>

    <div style="<?php echo "background-image:" . createProgressGradient($task) ?>"
         class="progress"><?php echo round($task["progress"]) ?>%
    </div>
</div>