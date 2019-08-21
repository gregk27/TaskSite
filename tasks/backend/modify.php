<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/include.php");
if (!isset($_POST["task"])) {
    echo "Task not specified";
    exit();
}

$task = getTask($_POST["task"]);

if (!inList(USER["ID"], $task["heads"])) {
    echo "Invalid permissions";
    exit();
}

if ($_POST["mode"] == "heads-wanted") {
    $stmt = $conn->prepare("UPDATE tasks.tasks SET headsWanted=? WHERE ID = ?");
    $val = $_POST["val"]=="true"? 1 : 0;
    $stmt->bind_param("ii", $val, $task["ID"]);
    $stmt->execute();
} else if ($_POST["mode"] == "help-wanted") {
    $stmt = $conn->prepare("UPDATE tasks.tasks SET helpWanted=? WHERE ID = ?");
    $val = $_POST["val"]=="true"? 1 : 0;
    $stmt->bind_param("ii", $val, $task["ID"]);
    $stmt->execute();
} else if ($_POST["mode"] == "invite-head"){
    #Get the list of heads
    $stmt=$conn->prepare("SELECT heads FROM tasks.tasks WHERE ID = ?");
    $stmt->bind_param("i", $task["ID"]);
    $stmt->execute();
    #Add the new head
    $heads = $stmt->get_result()->fetch_assoc()["heads"]. ",".getUsers()[$_POST["val"]];
    $stmt->close();
    #Update the list
    $stmt=$conn->prepare("UPDATE tasks.tasks SET heads = ? WHERE ID = ?");
    $stmt->bind_param("si", $heads, $task["ID"]);
    $stmt->execute();
}