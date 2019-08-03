<?php

$sql = "SELECT * from tasks.tasks WHERE (parent=?";
$args = array();
array_push($args, "i");
array_push($args, $topLevel);



$stmt = $conn->prepare("SELECT ID FROM tasks.tasks WHERE parent = ?");

function getChildren($ID){
    global $stmt;
    $stmt->bind_param("i", $ID);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $out = array();
    foreach($res as $r){
        array_push($out, $r["ID"]);
        $out = array_merge($out, getChildren($r["ID"]));
    }
    return $out;
}

if(isGet("subtasks", "on")){
    foreach(getChildren($topLevel) as $child){
        $sql .= " OR parent=?";
        $args[0] .= "i";
        array_push($args, $child);
    }
}

$sql .= ")";

if(isGet("filter-subteam", "on")){
    $sql .= " AND (subteams REGEXP CONCAT('[[:<:]]',?,'[[:>:]]'))";
    $match = "";
    foreach($_GET as $name=>$val){
        if(preg_match("/^team-(\d+)/", $name, $res) && $val == "on"){
            $match .= $res[1]."|";
            //Match parent if it's indeterminate (currently disabled);
//            foreach(SUBTEAMS as $s){
//                if(preg_match("/\b".$res[1]."\b/", $s["children"])){
//                    $match.=$s["ID"]."|";
//                }
//            }
        }
    }
    $match = implode('|',array_unique(explode('|', $match)));
    if(strlen($match) > 0){
        $match = rtrim($match, "|");
    }

    //Update params
    $args[0].="s";
    array_push($args, $match);
}

if(isGet("filter-prog", "on")){
    if(isGet("use-min-prog", "on")){
        $sql .= " AND progress > ?";
        $args[0] .= "i";
        array_push($args, $_GET["min-prog"]);
    }
    if(isGet("use-max-prog", "on")){
        $sql .= " AND progress < ?";
        $args[0] .= "i";
        array_push($args, $_GET["max-prog"]);
    }
}

if(isGet("filter-role", "on")){
    $sql.=" AND (";
        if(isGet("role-head", "on")){
            $sql.="(heads REGEXP CONCAT('[[:<:]]',?,'[[:>:]]')) OR";
            $args[0].="i";
            array_push($args, USER["ID"]);
        }
    if(isGet("role-joined", "on")){
        $sql.="(contributors REGEXP CONCAT('[[:<:]]',?,'[[:>:]]')) OR";
        $args[0].="i";
        array_push($args, USER["ID"]);
    }
    if(isGet("role-follow", "on")){
        $sql.="(followers REGEXP CONCAT('[[:<:]]',?,'[[:>:]]')) OR";
        $args[0].="i";
        array_push($args, USER["ID"]);
    }
    $sql.=" 0)";
}

$stmt = $conn->prepare($sql);
call_user_func_array(array($stmt, 'bind_param'), refValues($args));
//$stmt->bind_param("i", $topLevel);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>


<script>
    function onChange(element, checkSames = true, checkParent = true) {

        console.log(element.name, checkSames, checkParent)

        if (checkSames) {
            let sames = document.getElementsByName(element.name);
            for (let i = 0; i < sames.length; i++) {
                if (sames[i] == element) continue; //Skip self
                sames[i].checked = element.checked;
                onChange(sames[i], false);
            }
        }

        if (checkParent) {
            let top = element.parentElement.parentElement;
            let boxes = top.querySelectorAll(".child input");
            let parent = top.getElementsByClassName("parent")[0].firstChild;
            // console.log(top);
            // console.log(boxes);
            //If the parent is clicked, update all children
            if (element.parentElement.classList.contains("parent")) {
                for (let i = 0; i < boxes.length; i++) {
                    console.log("From parent");
                    setTimeout(function () {
                        boxes[i].checked = element.checked;
                        onChange(boxes[i], true, false);
                    }, 75 * i);
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
            target.style.paddingBottom = "0px";
            console.log(target.style.height)
        } else {
            // let height = 0;
            // let children = target.children;
            // for (let i = 0; i < children.length; i++) {
            //     height += children[i].clientHeight + 5;
            // }
            // console.log(height);
            // target.style.height = height + "px";

            //Compute height
            target.style.height = "auto";
            let height = target.clientHeight;
            //Reset for transition
            target.style.height = "0px";
            target.style.paddingBottom = "7px";
            //Delay to ensure transition playback
            setTimeout(function () {
                target.style.height = height + "px"
            }, 10);
        }
    }

    function showValue(element) {
        if (element.previousElementSibling == null) {
            element.nextElementSibling.value = element.value;
        } else {
            element.previousElementSibling.value = element.value;
        }
    }

</script>

    <div class="sidebar">
        <form method="GET" id="filter">
            <?php
            //Special case for subtasks page
            if(isset($_GET["task"])){
                echo "<input type='hidden' name='task' value='".$_GET["task"]."'/>";
            }

            ?>

            <h3>Filter</h3> <input type="submit" class="button active" value="Apply"
                                   style="float:right; margin-top:-35px;"/>

            <div class="checkbox"><input name="subtasks" id="subtasks" type="checkbox"/><label for="subtasks">Include
                    subtasks</label></div>
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

            <div class="checkbox"><input name="filter-prog" id="filter-prog" type="checkbox"
                                         onclick="toggle(this)"/><label for="filter-prog">By progress</label></div>
            <div class="enableSet" id="prog" style="width:175px">
                <div class="checkbox"><input name="use-min-prog" id="use-min-prog" type="checkbox"/><label
                            for="use-min-prog">Min progress</label></div>
                <label style="width:100%">
                    <input oninput="showValue(this)" class="slider" type="range" name="min-prog" value="20" min="0"
                           max="99" step="5"/>
                    <input oninput="showValue(this)" class="purple" id="value" type="text" value="20" maxlength="2"/>
                    <span class="purple unit" style="margin-left:-22px">%</span>
                </label>

                <div class="checkbox"><input name="use-max-prog" id="use-max-prog" type="checkbox"/><label
                            for="use-max-prog">Max progress</label></div>

                <label>
                    <input oninput="showValue(this)" class="slider" type="range" name="max-prog" value="20" min="0"
                           max="99" step="5"/>
                    <input oninput="showValue(this)" class="purple" id="value" type="number" value="20" maxlength="2"/>
                    <span class="purple unit" style="margin-left:-22px">%</span>
                </label>
            </div>

            <?php if(!VALID) echo "<!--" ?>
            <div class="checkbox"><input name="filter-role" id="filter-role" type="checkbox"
                                         onclick="toggle(this)"/><label for="filter-role">My role</label></div>
            <div class="enableSet" id="prog" style="width:175px">
                <div class="checkbox"><input name="role-head" id="role-head" type="checkbox"/><label
                            for="role-head">Head</label></div>

                <div class="checkbox"><input name="role-joined" id="role-joined" type="checkbox"/><label
                            for="role-joined">Contributor</label></div>
                <div class="checkbox"><input name="role-follow" id="role-follow" type="checkbox"/><label
                            for="role-follow">Following</label></div>
            </div>
            <?php if(!VALID) echo "-->"?>

        </form>
    </div>
    <div class='content'>
        <?php
        if(count($tasks) == 0){
            echo '<div class="error">No tasks found</div>';
        }
        foreach ($tasks as $task) {
            $fullTitle = true;
            include("../card.php");
        } ?>
    </div>

<script>
    let parameters = location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (let i = 0; i < parameters.length; i++) {
        let name = parameters[i].split("=")[0];
        let targets = document.getElementsByName(name);
        let value = parameters[i].split("=")[1];
        //If it's a button
        if (value == "on") {
            targets[0].checked = false;
            targets[0].click();
        } else {
            for (let i = 0; i < targets.length; i++) {
                console.log(targets[i]);
                targets[i].value = value;
            }
        }
    }

    //Set slider labels
    let sliders = document.getElementsByClassName("slider");
    for (let i = 0; i < sliders.length; i++) {
        sliders[i].nextElementSibling.value = sliders[i].value;
    }
</script>