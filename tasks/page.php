<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Usbwebserver</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
</head>
<body>
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

// Create title
$title = $task ["name"];
$stmt = $conn->prepare("SELECT name,parent FROM tasks.tasks WHERE ID = ?");
$val = $task ["parent"];
$res = "temp";
$stmt->bind_param("i", $val);
$stmt->bind_result($res, $val);
// Iterate over parents until top-level is found
while ($val != -1) {
    $tempID = $val;
    $stmt->execute();
    $stmt->fetch();
    $title = "<a class='plain' href='?task=" . $tempID . "'>" . $res . "</a>&nbsp> " . $title;
}
$stmt->close();

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
if ($level == 4 && $task ["joined"]) {
    $canPost = true;
}
?>
<div class="task-page-top" id="top">
    <div class="buttons" style="display:<?php echo $task["head"] ? "block" : "none" ?>">
        <?php $max = $task["unassigned"] == $task["local"];
        $min = $task["local"] == 0;
        if ($min) echo "<!--"; ?>
        <button onclick="setProgress(<?php echo $task["ID"] ?>, -5, 'top')" id="change">-5</button>
        <button onclick="setProgress(<?php echo $task["ID"] ?>, -1, 'top')" id="change">-1</button>
        <?php if ($min) echo "--> <button id='change'>Min hit</button>"; ?>
        &nbsp&nbsp
        <?php if ($max) echo "<!--"; ?>
        <button onclick="setProgress(<?php echo $task["ID"] ?>, 1, 'top')" id="change">+1</button>
        <button onclick="setProgress(<?php echo $task["ID"] ?>, 5, 'top')" id="change">+5</button>
        <?php if ($max) echo "--> <button id='change'>Max hit</button>"; ?>
    </div>
    <h2><?php echo $title ?></h2>
    <div style="<?php echo "background-image:linear-gradient(120deg, green " . ($task["progress"] - 5) . "%, gray " . ($task["progress"] + 5) . "%)" ?>"
         class="progress"><?php echo round($task["progress"]) ?>%
    </div>
</div>
<div id="below-top">
    <div class="task-page-content">
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
                   class="<?php echo $level == 3 ? 'underline' : '' ?>">Chat</a> <a
                        href="?task=<?php echo $task["ID"] ?>&lv=4"
                        class="<?php echo $level == 4 ? 'underline' : '' ?>">Subtasks</a>
                <?php echo $canPost ? '<a id="interact" class="button active" style="float:right" onclick="showDiag()">New</a>' : ''; ?>
            </nav>
            <div id="content">
                <?php
                if ($level == 4) {
                    $stmt = $conn->prepare("SELECT * FROM tasks.tasks WHERE parent = ?");
                    $stmt->bind_param("i", $taskID);
                    $stmt->execute();
                    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                    if (count($result) == 0) {
                        echo "<div style='text-align:center; background-color:#e9e9e9; padding:25px 100px; margin:50px;'>No subtasks found.</div>";
                    }
                    $temp = $task; //Save the task for after
                    foreach ($result as $task) {
                        include("small.php");
                    }
                    $task = $temp;//Re-set the task variable
                    $stmt->close();
                } else if ($level == 3) {
                    echo "<div style='text-align:center; background-color:#e9e9e9; padding:25px 100px; margin:50px;'>Live(ish) chat will be added. Eventually.</div>";
                } else {
                    foreach ($topics as $topic) {
                        include("topic.php");
                    }

                    if (count($topics) == 0) {
                        echo "<div style='text-align:center; background-color:#e9e9e9; padding:25px 100px; margin:50px;'>No one has posted yet.</div>";
                    }
                }
                ?>
            </div>
        </div>
        <?php
        for ($i = 0; $i < 100; $i++) {
            echo "<br/>";
        }
        ?>
    </div>
</div>

<div class="task-page-sidebar" id="sidebar">
    <div id="buttons">
        <?php if ($task["head"] || !isset($_COOKIE["token"])) {
            echo "<!--";
        } ?>
        <div
                onclick="sendRequest('contribute')"
                class="button <?php if ($task["joined"]) {
                    echo "de";
                }
                echo "active"; ?>"
                style="width: 38%; float: left;"><?php if ($task["joined"]) {
                echo "Quit";
            } else {
                echo "Join";
            } ?></div>
        <div onclick="sendRequest('follow')"
             class="button <?php if ($task["following"]) {
                 echo "de";
             }
             echo "active"; ?>"
             style="width: 44%; float: right;"><?php if ($task["following"]) {
                echo "Unfollow";
            } else {
                echo "Follow";
            } ?></div>
        <?php
        if ($task ["head"]) {
            echo "--><div class = 'button active'>Settings</div>";
        }
        if (USER["ID"] == -1) {
            echo "--><div class = 'button deactive'>Please login</div>";
        }
        ?>
    </div>

    <h3>Subtasks</h3>
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
                if ($min) echo "<!--";
                else echo '<button onclick = "setProgress(' . $sub["ID"] . ', -5, \'sidebar,top,tsk' . $sub["ID"] . '\')" id="change">-5</button>
							<button onclick = "setProgress(' . $sub["ID"] . ', -1, \'sidebar,top,tsk' . $sub["ID"] . '\')" id="change">-1</button>';
                if ($min) echo "--> <button id='change'>Min hit</button>";
                echo '&nbsp&nbsp';
                if ($max) echo "<!--";
                else echo '<button onclick = "setProgress(' . $sub["ID"] . ', 1, \'sidebar,top,tsk' . $sub["ID"] . '\')" id="change">+1</button>
							<button onclick = "setProgress(' . $sub["ID"] . ', 5, \'sidebar,top,tsk' . $sub["ID"] . '\')" id="change">+5</button>';
                if ($max) echo "--> <button id='change'>Max hit</button>";
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
</div>
</body>
</html>

<script>
    //Get the header
    var head = document.getElementById("top");
    var subs = document.getElementById("sidebar");
    var main = document.getElementsByClassName("task-page-content")[0];
    var body = document.getElementById("below-top");

    //Get the offset position of the navbar
    var pos = head.offsetTop;
    var subPos = subs.offsetTop;
    var subPosX = subs.offsetLeft;

    xhttp = new XMLHttpRequest();

    //Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
    function sticky() {
// 	main.offsetTop = 244;
        console.log(window.pageYOffset + "\t" + main.offsetTop);
        if (window.pageYOffset > pos) {
            body.style["padding-top"] = "75px";
            head.classList.add("sticky");
        } else {
            head.classList.remove("sticky");
            body.style["padding-top"] = "0px";
        }
        if (window.pageYOffset > subPos - 45) {
            subs.style.left = subPosX + "px";
            subs.classList.add("sticky");
        } else {
            subs.classList.remove("sticky");
        }
    }

    function resize() {
        console.log(subPosX);
        subs.classList.remove("sticky");
        subPosX = subs.offsetLeft;
        console.log(subPosX);
        sticky();
    }

    document.onscroll = sticky;
    window.addEventListener("resize", resize);

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

    function sendRequest(mode) {
        xhttp.open("POST", "/tasks/join.php", false);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("task=" + <?php echo $task["ID"]?> + "&mode=" + mode);

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
if ($canPost && $level == 4) {
    include "newtask.html";
} else if ($canPost) {
    include "newtopic.html";
}
?>