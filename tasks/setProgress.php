<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include.php");
$ID = $_POST["task"];
$delta = $_POST["delta"];
$stmt = $conn->prepare("SELECT * FROM tasks.tasks WHERE ID = ?");
$stmt->bind_param("i", $ID);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
if (!defined("TOP")) define("TOP", $ID);
$uID = USER["ID"];

echo "<h2>" . $_POST["task"] . "</h2>";

$update = $conn->prepare("UPDATE tasks.tasks SET progress=? WHERE ID=?");
$local = $conn->prepare("UPDATE tasks.tasks SET local=? WHERE ID = ?");

echo "Delta: " . $_POST["delta"] . "<br/>";
echo "Progress: " . $task["progress"] . "<br/>";

$newVal = $task["progress"];

if (TOP == $ID) {
    echo "Top-level<br/>";
    if (!hasPerms($ID, 0, $uID)) {
        echo "Invalid permissions";
        exit();
    }

    echo "Old Local: " . $task["local"] . "<br/>";
    $task["local"] += $delta;

    if ($task["local"] > $task["unassigned"]) {
        $delta -= $task["local"] - $task["unassigned"];
        $task["local"] = $task["unassigned"];
    } else if ($task["local"] < 0) {
        $delta -= $task["local"];
        $task["local"] = 0;
    }

    $local->bind_param("di", $task["local"], $ID);
    $local->execute();
    echo "New Local: " . $task["local"] . "<br/>";
}
$newVal = $task["progress"] + $delta;
if ($newVal > 100) {
    $delta -=100-$newVal;
    $newVal = 100;
}if ($newVal < 0) {
    $delta -= $newVal;
    $newVal = 0;
}
echo "New value: " . $newVal . "<br/>";
$update->bind_param("di", $newVal, $ID);
$update->execute();

if ($task["parent"] != -1) {
    $weighted = $delta * ($task["weight"] / 100);
    echo "Weighted value: " . $weighted;
    $_POST["task"] = $task["parent"];
    $_POST["delta"] = $weighted;
    echo "<br/>";
    include("setProgress.php");
}