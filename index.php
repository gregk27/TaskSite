<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Usbwebserver</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
</head>

<script>
    function onChange(element, checkSames = true, checkParent = true) {

        console.log(element.name, checkSames, checkParent)

        if (checkSames) {
            let sames = document.getElementsByName(element.name);
            for (let i = 0; i < sames.length; i++) {
                if(sames[i] == element) continue; //Skip self
                sames[i].checked = element.checked;
                onChange(sames[i], false);
            }
        }

        if(checkParent) {
            let top = element.parentElement.parentElement;
            let boxes = top.querySelectorAll(".child input");
            let parent = top.getElementsByClassName("parent")[0].firstChild;
            // console.log(top);
            // console.log(boxes);
            //If the parent is clicked, update all children
            if (element.parentElement.classList.contains("parent")) {
                for (let i = 0; i < boxes.length; i++) {
                    console.log("From parent");
                    // setTimeout(function () {
                        boxes[i].checked = element.checked;
                        onChange(boxes[i], true, false);
                    // }, 75 * i);
                }
            }
            //If the child is clicked, update the parent
            else if (element.parentElement.classList.contains("child")) {
                let state = element.checked;
                let same = true;
                for (let i = 0; i < boxes.length; i++) {
                    if (boxes[i].checked != state) {
                        same = false;
                    }
                }
                if (same) {
                    parent.checked = state;
                    parent.indeterminate = false
                } else {
                    parent.checked = false;
                    parent.indeterminate = true;
                }

            }
        }
    }

    function toggle(element) {
        let target = element.parentElement.nextElementSibling;
        console.log(target);
        if (!element.checked) {
            target.style.height = "0px";
            console.log(target.style.height)
        } else {
            let height = 0;
            let children = target.children;
            for (let i = 0; i < children.length; i++) {
                height += children[i].clientHeight + 5;
            }
            console.log(height);
            target.style.height = height + "px";
        }
    }


</script>

<body>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/header.php");

$stmt = $conn->prepare("SELECT * FROM `tasks`.`tasks` WHERE parent = -1");
$stmt->execute();
$result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT * FROM tasks.tasks WHERE parent = ?");
?>

<div class='below-top'>
    <div class="sidebar">
        <form method="GET">
            <h3>Filter</h3> <input type="submit" class="button active" value="Apply"
                                   style="float:right; margin-top:-35px;"/>

            <div class="checkbox"><input name="subtasks" id="subtasks" type="checkbox"/><label for="subtasks">Include subtasks</label></div>
            <?php

            echo '<div class="checkbox"><input name="filter-subteam" id="subteams" type="checkbox" onclick="toggle(this)"/><label for="subteams">By subteam</label></div>';
            echo '<div class="enableSet" id="subteams">';
            foreach (SUBTEAMS as $sub) {
                if ($sub["ID"] == 0 || $sub["isChild"]) continue;
                echo '<div class="checkset"><div class="checkbox parent"><input name="team-' . $sub["ID"] . '" id="check-' . $sub["ID"] . '" type="checkbox" onclick="onChange(this)"/><label for="check-' . $sub["ID"] . '">' . $sub["name"] . '</label></div>';
                foreach (array_filter(explode(",", $sub["children"])) as $cID) {
                    $child = SUBTEAMS[$cID];
                    echo '<div class="checkbox child"><input name="team-' . $child["ID"] . '" id="check-' . $child["ID"] . '" type="checkbox" onclick="onChange(this)"/><label for="check-' . $child["ID"] . '">' . $child["name"] . '</label></div>';
                }
                echo '</div>';
            }
            echo "</div>";
            ?>
        </form>
    </div>
    <div class='content'>
        <?php
        foreach ($result as $task) {

            include("tasks/small.php");
        } ?>
    </div>
</div>


<?php
$stmt->close();
$conn->close();

?>

<script>
    let parameters = location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (let i = 0; i < parameters.length; i++) {
        let name = parameters[i].split("=")[0];
        //If it's a button
        if(parameters[i].split("=")[1] == "on") {
            target = document.getElementsByName(name)[0];
            target.checked = false;
            target.click();
        }
    }
</script>
</body>
