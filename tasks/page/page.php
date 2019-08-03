<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Usbwebserver</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
</head>
<body id="task-page">
<?php
//Ensure that level is set, if not the default is announcements
if (!isset($_GET["lv"])) {
        header("Location: " . rtrim($_SERVER["REQUEST_URI"], "/") . "/ann");
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/header.php");

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


<?php include("components/top.php") ?>
<div class="below-top">

    <?php include("components/sidebar.php") ?>
    <div class="content">
        <div class="description">
            <h2>About</h2>
            <?php echo $task["description"] ?>
        </div>
        <div>
            <nav><a href="ann" class="<?php echo $level == 0 ? 'underline' : '' ?>">Announcements</a>
                <a href="prog" class="<?php echo $level == 1 ? 'underline' : '' ?>">Progress</a>
                <a href="disc" class="<?php echo $level == 2 ? 'underline' : '' ?>">Discussion</a>
                <a href="chat" class="<?php echo $level == 3 ? 'underline' : '' ?>">Chat</a>
                <?php echo $canPost ? '<a id="interact" class="button active" style="float:right" onclick="showDiag(\'new-topic\')">New</a>' : ''; ?>
            </nav>
            <div id="content">
                <?php
                if ($level == 3) {
                    echo "<div class='error'>Live(ish) chat will be added. Eventually.</div>";
                } else {
                    foreach ($topics as $topic) {
                        include("topic/topic.php");
                    }

                    if (count($topics) == 0) {
                        echo "<div class='error'>No one has posted yet.</div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>

</div>
</body>
</html>

<script>


    function setVal(url, param, value) {
        console.log(url);
        console.log(url.indexOf(param));
        console.log(param + "=" + value);
        if (url.indexOf('?') < 0) {
            url += '?' + param + '=' + value;
        } else if (url.indexOf(param) > 0) {
            url = url.replace(new RegExp("(" + param + "=[^&\s]*)", 'g'), param + "=" + value);
        } else {
            url += '&' + param + '=' + value;
        }

        console.log(url);
        return url;
    }

    function load() {
        var links = document.getElementsByClassName("pointer");
        for (var i = 0; i < links.length; i++) {
            links[i].href = setVal(window.location.href, "focus", links[i].id);
        }


        if (document.getElementsByClassName("scrollto")[0] != null) {
            document.getElementsByClassName("scrollto")[0].scrollIntoView();
            window.scrollBy(0, -100);
        }
    }

    window.onload = load;

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
<?php
if ($task["head"]) include "popup/newtask.html";
if ($canPost) {
    include "popup/newtopic.html";
}
?>