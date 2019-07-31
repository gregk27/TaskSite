<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Usbwebserver</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
</head>

<script>
    function onChange(element, checkSames=true) {
        if(checkSames) {
            let sames = document.getElementsByName(element.name);
            for (let i = 0; i < sames.length; i++) {
                sames[i].checked = element.checked;
                onChange(sames[i], false);
            }
        }

        let top = element.parentElement.parentElement;
        let boxes = top.querySelectorAll(".child input");
        let parent = top.getElementsByClassName("parent")[0].firstChild;
        console.log(top);
        console.log(boxes);
        //If the parent is clicked, update all children
        if (element.parentElement.classList.contains("parent")) {
            for (let i = 0; i < boxes.length; i++) {
                setTimeout(function () {
                    boxes[i].checked = element.checked;
                    onChange(boxes[i]);
                }, 75 * i);
            }
        }
        //If the child is clicked, update the parent
        if (element.parentElement.classList.contains("child")) {
            let state = element.checked;
            let same = true;
            for (let i = 0; i < boxes.length; i++) {
                console.log(i+":"+boxes[i].checked+" -- "+state);
                if(boxes[i].checked != state){
                    same = false;
                }
            }
            console.log(same);
            if(same){ parent.checked = state; parent.indeterminate=false}
            else parent.indeterminate = true;

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
        <h3>Filter</h3>
        <form method="GET">
            <?php
            foreach (SUBTEAMS as $sub) {
                if ($sub["ID"] == 0 || $sub["isChild"]) continue;
                echo '<div class="checkset"><div class="checkbox parent"><input name="' . $sub["name"] . '" id="check-' . $sub["ID"] . '" type="checkbox" onclick="onChange(this)"/><label for="check-' . $sub["ID"] . '">' . $sub["name"] . '</label></div>';
                foreach(array_filter(explode(",", $sub["children"])) as $cID){
                    $child = SUBTEAMS[$cID];
                   echo  '<div class="checkbox child"><input name="' . $child["name"] . '" id="check-' . $child["ID"] . '" type="checkbox" onclick="onChange(this)"/><label for="check-' . $child["ID"] . '">' . $child["name"] . '</label></div>';
                }
                echo '</div>';
            }
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
</body>
