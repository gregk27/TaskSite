<?php
$uID = $_POST ["token"]; // $_COOKIE["token"];

include $_SERVER ['DOCUMENT_ROOT'] . "/passwords.php";

$conn = new mysqli ($dbAddress, $dbUser, $dbPass);
// TODO: Remove after debugging
mysqli_report (MYSQLI_REPORT_ALL);

// TaskID: The ID of the parent task
// level: 0 for head, 1 for contributor, 2 for registered user
function hasPerms($taskID, $level) {
	global $conn, $uID;
	// If the taskID is an integer value, than it's not a risk for injection
	if (! is_int (( int ) $taskID)) {
		echo "ERROR: Page Error";
		return false;
	}
	
	// TOOD: Check if the userID is registered
	if ($level == 2) {
		return isset ($_POST ["token"]);
	}
	
	$users = $conn->query ("SELECT heads,contributors FROM tasks.tasks WHERE ID = " . $taskID)->fetch_assoc ();
	$isHead = preg_match ("/\|" . $uID . "\b/", $users ['heads']);
	$isCont = preg_match ("/\|" . $uID . "\b/", $users ["contributors"]);
	
	if ($level == 0 && $isHead)
		return true;
	else if ($level == 1 && ($isHead || $isCont))
		return true;
	
	echo "Invalid permissions";
	return false;
}

if (! ISSET ($_POST ["mode"])) {
	echo "ERROR: Page Error";
} else if ($_POST ["mode"] == "task") {
	$parent = $_POST ["parent"];
	$name = $_POST ["name"];
	$subteams = $_POST ["subteams"];
	$desc = $_POST ["desc"];
	$heads = $_POST ["heads"];
	
	if ($parent == - 1) { // Special permission check for top-level tasks
	} else if (hasPerms ($parent, 0)) {
		echo "Has permission";
		$stmt = $conn->prepare ("INSERT INTO tasks.tasks(parent,name,subteams,description,heads) VALUES (?,?,?,?,?)");
		$stmt->bind_param ("issss", $parent, $name, $subteams, $desc, $heads);
		$stmt->execute ();
		$stmt->close ();
	}
} else if ($_POST ["mode"] == "topic") {
	$level = $_POST ["level"];
	$title = $_POST ["title"];
	$uName = $_POST ["user"];
	$time = time ();
	$text = $_POST ["text"];
	$taskID = $_POST ["task"];
	
	if (hasPerms ($taskID, $level)) {
		echo "Has permission";
		$stmt = $conn->prepare ("INSERT INTO tasks.topics(level,title,user,time,text,taskID) VALUES (?,?,?,?,?,?)");
		$stmt->bind_param ("issisi", $level, $title, $uName, $time, $text, $taskID);
		$stmt->execute ();
		$stmt->close ();
	}
} else if ($_POST ["mode"] == "reply") {
	$taskID = $_POST["task"];
	$level = $_POST ["level"];
	$parent = $_POST ["parent"];
	$uName = $_POST ["user"];
	$time = time ();
	$text = $_POST ["text"];
	
	// Up the level by one, so that lower-perms can reply
	if (hasPerms ($taskID, $level + 1)) {
		echo "Has permission";
		$stmt = $conn->prepare ("INSERT INTO tasks.replies(parentID,user,time,text) VALUES (?,?,?,?)");
		$stmt->bind_param ("isis", $parent, $uName, $time, $text);
		$stmt->execute ();
		$stmt->close ();
	}
}

?>