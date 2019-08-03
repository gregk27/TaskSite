<?php include("scripts.php")?>

    <div class="sidebar">
        <?php include("form.php")?>
    </div>
    <div class='content'>
        <?php
        if(count($tasks) == 0){
            echo '<div class="error">No tasks found</div>';
        }
        foreach ($tasks as $task) {
            $fullTitle = true;
            include($_SERVER["DOCUMENT_ROOT"]."/tasks/card.php");
        } ?>
    </div>

