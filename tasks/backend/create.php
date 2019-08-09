<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include.php");
$uID = USER["ID"];
if($uID == -1){
    exit();
}


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
    if($sub1 != 0) {
        array_push($subteams, $sub1);
    }
    if($sub2 != 0) {
        array_push($subteams, $sub2);
    }
    if($sub3 != 0) {
        array_push($subteams, $sub3);
    }

    $subteams = implode(",", $subteams);

    echo "<br/>".$subteams;

    $heads = array();

    foreach ($headlist as $h) {
        array_push($heads, $h);
    }

    $heads = implode(",", $heads);

    echo "<br/>".$heads;

    if ($parent == -1) { // Special permission check for top-level tasks
    } else if (hasPerms($parent, 0, $uID)) {
        echo "Has permission";
        $stmt = $conn->prepare("INSERT INTO tasks.tasks(parent,name,subteams,description,heads) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss", $parent, $name, $subteams, $desc, $heads);
		$stmt->execute ();
        $ID = $stmt->insert_id;
        $stmt->close();

        $_POST["delta"] = $weight;
        $_POST["task"] = $ID;
        include("setWeight.php");
    }
} else if ($_POST ["mode"] == "topic") {
    $level = $_POST ["level"];
    $title = cleanString($_POST ["title"]);
    $time = time();
    $text = formatString($_POST ["text"]);
    $taskID = $_POST ["task"];

    if (hasPerms($taskID, $level, $uID)) {
        echo "Has permission";
        $stmt = $conn->prepare("INSERT INTO tasks.topics(level,title,users,time,text,taskID) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("issisi", $level, $title, $uID, $time, $text, $taskID);
        $stmt->execute();
        $stmt->close();
    }
} else if ($_POST ["mode"] == "reply") {
    $taskID = $_POST["task"];
    $level = $_POST ["level"];
    $parent = $_POST ["parent"];
    $time = time();
    $text = formatString($_POST ["text"]);

    // Up the level by one, so that lower-perms can reply
    if (hasPerms($taskID, $level + 1, $uID)) {
        echo "Has permission";
        $stmt = $conn->prepare("INSERT INTO tasks.replies(parentID,users,time,text) VALUES (?,?,?,?)");
        $stmt->bind_param("isis", $parent, $uID, $time, $text);
        $stmt->execute();
        $stmt->close();
    }
}

?>
