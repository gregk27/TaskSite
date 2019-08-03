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

// If the users is a head, they are involved
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

<?php include("components/top.php") ?>

<div class='below-top'>

    <?php
    $topLevel = $_GET["task"];
    $stopAt = $topLevel;
    include("../filterPage/filterPage.php");

    ?>

</div>
</body>


<script>
    function setProgress(task, delta, refreshID) {
        xhttp.open("POST", "/tasks/backend/setProgress.php", false);
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
   if($task["head"]) include("popup/newtask.html");
?>