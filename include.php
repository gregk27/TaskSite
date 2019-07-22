<!--Include the main CSS file-->
<link href="/style.css" rel="stylesheet" type="text/css" media="screen"/>

<?php
include_once("passwords.php");
$conn = new mysqli ($dbAddress, $dbUser, $dbPass);
// TODO: Remove after debugging
mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);


// TaskID: The ID of the parent task
// level: 0 for head, 1 for contributor, 2 for registered user
function hasPerms($taskID, $level, $uID) {
    global $conn;

    // If the taskID is an integer value, than it's not a risk for injection
    if (!is_int(( int )$taskID)) {
        echo "ERROR: Page Error";
        return false;
    }

    // TOOD: Check if the userID is registered
    if ($level >= 2 && isUser($uID)) {
        return isset ($_POST ["token"]);
    }

    $users = $conn->query("SELECT heads,contributors FROM tasks.tasks WHERE ID = " . $taskID)->fetch_assoc();
    $isHead = preg_match("/\|" . $uID . "\b/", $users ['heads']);
    $isCont = preg_match("/\|" . $uID . "\b/", $users ["contributors"]);

    if ($level == 0 && $isHead)
        return true;
    else if ($level == 1 && ($isHead || $isCont))
        return true;

    echo "Invalid permissions";
    return false;
}

function isUser($uID) {
    global $conn;
    //If the ID is an int, then it isn't injection
    if (!is_int(( int )$uID)) {
        return false;
    }
    $result = $conn->query("SELECT username FROM tasks.users WHERE ID = " . $uID)->fetch_assoc();
    return count($result) > 0;
}

function getUser($uID) {
    global $conn;
    return $conn->query("SELECT username FROM tasks.users WHERE ID = " . $uID)->fetch_assoc();
}

?>