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

if(isGet("filter-demand", "on")){
    if(isGet("demand-head", "on")) {
        $sql .= " AND headsWanted";
    }if(isGet("demand-help", "on")) {
        $sql .= " AND helpWanted";
    }
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
