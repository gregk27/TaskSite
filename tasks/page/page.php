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
    header("Location: " . $_SERVER["REQUEST_URI"] . "&lv=0");
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
        } ?>
    </div>
    <h2 class="task-name"><?php echo $title ?></h2>

    <div style="<?php echo "background-image:".createProgressGradient($task) ?>"
         class="progress"><?php echo round($task["progress"]) ?>%
    </div>
</div>
<div class="below-top">

    <div class="task-page sidebar" id="sidebar">
        <?php if (!VALID) echo "<!--"; ?>
        <div id="buttons" style="margin-left:-15px; margin-right:-35px;">
            <?php if ($task["head"]) {
                newButton("console.log('TODO')", true, "Options", true, "width:70%");
            } else {
                newButton("sendRequest('contribute')", !$task["joined"], $task["joined"] ? "Quit" : "Join", true, "width: 38%; float: left;");
                newButton("sendRequest('follow')", !$task["following"], $task["following"] ? "Unfollow" : "Follow", true, "width:54%; float: right;");
            } ?>
        </div>
        <?php if (!VALID) echo "-->"; ?>

        <h3><a class="plain" href="subtasks.php?task=<?php echo $taskID?>">Subtasks</a></h3>
        <table>
            <?php
            if (count($subtasks) == 0) {
                echo "<tr><td>No subtasks</td></tr>";
            }
            foreach ($subtasks as $sub) {
                echo '<tr id="task">
					<td id="name"><a class="plain" href="?task=' . $sub ["ID"] . '">' . $sub ["name"] . '</a></td>
					<td id="percent">' . round($sub ["progress"]) . '%</td>
				</tr>';
                if (inList(USER["ID"], $sub ["heads"])) {
                    $max = $sub["unassigned"] == $sub["local"];
                    $min = $sub["local"] == 0;
                    echo '<tr>
						<td id="config" colspan="2">';
                    if ($min) newButton("", false, "Min hit", false, "font-size:15px; padding:4px 10px;");
                    else {
                        newButton("setProgress(" . $sub["ID"] . ", -5, 'sidebar,name,tsk')", true, "-5", true, null, "change");
                        newButton("setProgress(" . $sub["ID"] . ", -1, 'sidebar,name,tsk')", true, "-1", true, null, "change");
                    }
                    echo '&nbsp&nbsp';
                    if ($max) newButton("", false, "Max hit", false, "font-size:15px; padding:4px 10px;");
                    else {
                        newButton("setProgress(" . $sub["ID"] . ", 1, 'sidebar,name,tsk')", true, "+1", true, null, "change");
                        newButton("setProgress(" . $sub["ID"] . ", 5, 'sidebar,name,tsk')", true, "+5", true, null, "change");
                    }
                    echo '</td>
					</tr>';
                }
            }
            ?>
        </table>
        <div
                style="width: 100%; background-color: black; height: 2px; margin-top: 5px; margin-bottom: 5px"></div>
        <h3>People</h3>
        <strong>Heads</strong>
        <ul>
            <?php

            foreach ($task ["heads"] as $head) {
                echo "<li>" . printName($head, false) . "</li>";
            }
            ?>
        </ul>
        <strong>Contributors</strong>
        <ul>
            <?php

            foreach ($task ["contributors"] as $cont) {
                echo "<li>" . printName($cont, false) . "</li>";
            }
            ?>
        </ul>
    </div>
    <div class="content">
        <div class="description">
            <h2>About</h2>
            <?php echo $task["description"] ?>
        </div>
        <div>
            <nav><a href="?task=<?php echo $task["ID"] ?>&lv=0"
                    class="<?php echo $level == 0 ? 'underline' : '' ?>">Announcements</a>
                <a href="?task=<?php echo $task["ID"] ?>&lv=1"
                   class="<?php echo $level == 1 ? 'underline' : '' ?>">Progress</a> <a
                        href="?task=<?php echo $task["ID"] ?>&lv=2"
                        class="<?php echo $level == 2 ? 'underline' : '' ?>">Discussion</a>
                <a href="?task=<?php echo $task["ID"] ?>&lv=3"
                   class="<?php echo $level == 3 ? 'underline' : '' ?>">Chat</a>
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
if($task["head"]) include "popup/newtask.html";
if ($canPost) {
    include "popup/newtopic.html";
}
?>