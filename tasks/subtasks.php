<body>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/header.php");

if (!isset($_GET["task"])) {
    echo "<div class='error'>Parent task not specified</div>";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM `tasks`.`tasks` WHERE `ID` = ?");
$stmt->bind_param("i", $_GET ["task"]);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
$stmt->close();

$task ["subteams"] = explode(",", $task ["subteams"]);
$task ["heads"] = explode(",", $task ["heads"]);
$task ["contributors"] = explode(",", $task ["contributors"]);
$task ["followers"] = explode(",", $task ["followers"]);
$task ["joined"] = false;
$task ["following"] = false;
$task ["head"] = false;

$stmt = $conn->prepare("SELECT * FROM `tasks`.`tasks` WHERE `parent` = ?");
$stmt->bind_param("i", $_GET ["task"]);
$stmt->execute();
$subtasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// If the user is a head, they are involved
if (inList(USER["ID"], $task["heads"])) {
    $task ["head"] = true;
    $task ["joined"] = true;
}
if (inList(USER["ID"], $task["contributors"])) {
    $task ["joined"] = true;
}
if (inList(USER["ID"], $task["followers"])) {
    $task ["following"] = true;
}

$level = 0;
if (ISSET ($_GET ["lv"])) {
    $level = $_GET ["lv"];
}
$taskID = -1;
if (ISSET ($_GET ["task"])) {
    $taskID = $_GET ["task"];
}

$title = fullPath($task["ID"]);

$stmt = $conn->prepare("SELECT * FROM `tasks`.`topics` WHERE taskID = ? AND level = ?");
$stmt->bind_param("ii", $taskID, $level);
$stmt->execute();
$topics = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="sticky-top" id="name">
    <div class="buttons" style="display:<?php echo $task["head"] ? "block" : "none" ?>">
        <?php $max = $task["unassigned"] == $task["local"];
        $min = $task["local"] == 0;
        newButton("showDiag('new-task')",true,"Create Subtask", true);
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
        }
        ?>
    </div>
    <h2 class="task-name">&nbsp&lt<?php echo $title ?></h2>
    <div style="<?php echo "background-image:linear-gradient(120deg, green " . ($task["progress"] - 5) . "%, gray " . ($task["progress"] + 5) . "%)" ?>"
         class="progress"><?php echo round($task["progress"]) ?>%
    </div>
</div>

<div class='below-top'>

    <?php
    $topLevel = $_GET["task"];
    $stopAt = $topLevel;
    include("filterPage.php");

    ?>

</div>
</body>


<script>
    function setProgress(task, delta, refreshID) {
        xhttp.open("POST", "setProgress.php", false);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("task=" + task + "&delta=" + delta);

        console.log(xhttp.responseText);

        xhttp.open("GET", window.location.href, false);
        xhttp.send();
        let text = xhttp.responseText;
        // console.log(text);
        let doc = new DOMParser().parseFromString(text, "text/html");
        // console.log(doc);
        // console.log(document);
        // console.log(topic);
        let ids = refreshID.split(",");
        for (let i = 0; i < ids.length; i++) {
            let msg = doc.getElementById(ids[i]);
            let page = document.getElementById(ids[i]);
            // console.log(msg);
            // console.log(page);
            if (page == null) continue;
            page.innerHTML = msg.innerHTML;
        }
    }
</script>

<?php
   if($task["head"]) include("newtask.html");
?>