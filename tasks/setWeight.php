<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include.php");
$ID = $_POST["task"];
$delta = $_POST["delta"];
$stmt = $conn->prepare("SELECT * FROM tasks.tasks WHERE ID = ?");
$stmt->bind_param("i", $ID);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();

if($task["parent"] == -1){
    echo "Invalid operation on a top-level task";
    exit();
}

$stmt->bind_param("i", $task["parent"]);
$stmt->execute();
$parent = $stmt->get_result()->fetch_assoc();

//Ensure that there is space to assign
if($parent["unassigned"] - $delta > 100){
    $delta = $parent["unassigned"]-100;
} else if ($parent["unassigned"] - $delta < 0){
    $delta = $parent["unassigned"];
}

//Change the weight value
$stmt = $conn->prepare("UPDATE tasks.tasks SET weight=? WHERE ID=?");
echo "Old weight: ".$task["weight"]."<br/>";
$newVal = $task["weight"]+$delta;
echo "New weight: ".$newVal."<br/>";
$stmt->bind_param("ii", $newVal, $ID);
$stmt->execute();
//Change parent local progress if needed to account for new assignment
$stmt = $conn->prepare("UPDATE tasks.tasks SET unassigned=?,local=?,progress=? WHERE ID=?");

echo "Old unassigned: ".$parent["unassigned"]."<br/>";
$newVal = $parent["unassigned"]-$delta;
echo "New unassigned: ".$newVal."<br/>";
if($parent["local"] > $newVal){ //Change progress, as needed
    $parent["progress"] -= $parent["local"] - $newVal;
    $parent["local"] = $newVal;
}

$parent["progress"] += $task["progress"]*($delta/100);

$stmt->bind_param("iddi", $newVal, $parent["local"], $parent["progress"], $task["parent"]);
$stmt->execute();

if (!defined("TOP")) define("TOP", $ID);
$uID = USER["ID"];


//Change progress values
$weighted = $delta * ($task["progress"] / 100);
echo "Changed progress: " . $weighted;
$_POST["task"] = $task["parent"];
$_POST["delta"] = $weighted;
echo "<br/>";
include("setProgress.php");