<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include.php");
$uID = USER["ID"]; // $_COOKIE["token"];

echo implode(",", $_POST);

if (!ISSET ($_POST ["mode"])) {
    echo "ERROR: Page Error";
    echo "Invalid mode";
} else if ($_POST ["mode"] == "task") {
    $parent = $_POST ["parent"];
    $name = cleanString($_POST ["name"]);
    $sub1 = cleanString($_POST ["team1"]);
    $sub2 = cleanString($_POST ["team2"]);
    $sub3 = cleanString($_POST ["team3"]);
    $desc = formatString($_POST ["desc"]);
    $headlist = explode(",", $_POST ["heads"]);
    $weight = 0;
    if (ISSET($_POST["weight"])) {
        $weight = $_POST["weight"];
    }

    $subteams = array();
    if($sub1 != "None") {
        array_push($subteams, $sub1);
    }
    if($sub2 != "None") {
        array_push($subteams, $sub2);
    }
    if($sub3 != "None") {
        array_push($subteams, $sub3);
    }

    $subteams = implode(",", $subteams);

    echo "<br/>".$subteams;

    $heads = array();

    $users = getUsers();
    foreach ($headlist as $h) {
        array_push($heads, $h."|".$users[$h]);
    }

    $heads = implode(",", $heads);

    echo "<br/>".$heads;

    if ($parent == -1) { // Special permission check for top-level tasks
    } else if (hasPerms($parent, 0, $uID)) {
        echo "Has permission";
        $stmt = $conn->prepare("INSERT INTO tasks.tasks(parent,name,subteams,description,heads,weight) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("issssi", $parent, $name, $subteams, $desc, $heads, $weight);
		$stmt->execute ();
        $stmt->close();
    }
} else if ($_POST ["mode"] == "topic") {
    $level = $_POST ["level"];
    $title = cleanString($_POST ["title"]);
    $uName = USER["name"];
    $time = time();
    $text = formatString($_POST ["text"]);
    $taskID = $_POST ["task"];

    if (hasPerms($taskID, $level, $uID)) {
        echo "Has permission";
        $stmt = $conn->prepare("INSERT INTO tasks.topics(level,title,user,time,text,taskID) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("issisi", $level, $title, $uName, $time, $text, $taskID);
        $stmt->execute();
        $stmt->close();
    }
} else if ($_POST ["mode"] == "reply") {
    $taskID = $_POST["task"];
    $level = $_POST ["level"];
    $parent = $_POST ["parent"];
    $uName = USER["name"];
    $time = time();
    $text = formatString($_POST ["text"]);

    // Up the level by one, so that lower-perms can reply
    if (hasPerms($taskID, $level + 1, $uID)) {
        echo "Has permission";
        $stmt = $conn->prepare("INSERT INTO tasks.replies(parentID,user,time,text) VALUES (?,?,?,?)");
        $stmt->bind_param("isis", $parent, $uName, $time, $text);
        $stmt->execute();
        $stmt->close();
    }
}

?>

<script>window.close()</script>
