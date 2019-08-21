<?php
$stmt = $conn->prepare("SELECT * FROM `tasks`.`tasks` WHERE `ID` = ?");
$stmt->bind_param("i", $_GET ["task"]);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
$stmt->close();

$task ["subteams"] = array_filter(explode(",", $task ["subteams"]));
$task ["heads"] = array_filter(explode(",", $task ["heads"]));
$task ["contributors"] = array_filter(explode(",", $task ["contributors"]));
$task ["followers"] = array_filter(explode(",", $task ["followers"]));
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

$canPost = false;
if ($level == 0 && $task ["head"]) {
    $canPost = true;
}
if ($level == 1 && $task ["joined"]) {
    $canPost = true;
}
if ($level == 2 && USER["ID"] > 0) {
    $canPost = true;
}

?>

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

    function sendRequest(mode) {
        xhttp.open("POST", "/tasks/backend/join.php", false);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("task=" + <?php echo $task["ID"]?> +"&mode=" + mode);

        console.log(xhttp.responseText);

        xhttp.open("GET", window.location.href, false);
        xhttp.send();
        let text = xhttp.responseText;
        // console.log(text);
        let doc = new DOMParser().parseFromString(text, "text/html");
        // console.log(doc);
        // console.log(document);
        // console.log(topic);
        let msg = doc.getElementById("sidebar");
        let page = document.getElementById("sidebar");
        // console.log(msg);
        // console.log(page);
        page.innerHTML = msg.innerHTML;

    }
</script>
