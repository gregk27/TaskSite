<!--Include the main CSS file-->
<link href="/style.css" rel="stylesheet" type="text/css" media="screen"/>

<?php
include_once("passwords.php");

$conn = new mysqli ($dbAddress, $dbUser, $dbPass);
// TODO: Remove after debugging
mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);

$usrStmt = $conn->prepare("SELECT username,ID FROM tasks.users WHERE ID = ?");
$allUsrStmt = $conn->prepare("SELECT username,ID FROM tasks.users");
$permsStmt = $conn->prepare("SELECT heads,contributors FROM tasks.tasks WHERE ID = ?");

define("SUBTEAMS", $conn->query("SELECT * FROM tasks.subteams")->fetch_all(MYSQLI_ASSOC));


if (ISSET($_COOKIE["token"])) {
    if (isUser($_COOKIE["token"])) {
        define("USER", getUser($_COOKIE["token"]));
    } else {
        //If the user doesn't exist, then delete the token to prevent issues
        setcookie("token", "", time() - 3600);
        header("Refresh:0");
    }
} else {
    define("USER", array("name" => "NULL", "ID" => -1));
}

// TaskID: The ID of the parent task
// level: 0 for head, 1 for contributor, 2 for registered user
function hasPerms($taskID, $level, $uID) {
    global $permsStmt;

    // If the taskID is an integer value, than it's not a risk for injection
    if (!is_int(( int )$taskID)) {
        echo "ERROR: Page Error";
        return false;
    }

    // TOOD: Check if the userID is registered
    if ($level >= 2 && isUser($uID)) {
        return isset ($_POST ["token"]);
    }

    $permsStmt->bind_param("i", $taskID);
    $permsStmt->execute();
    $users = $permsStmt->execute()->fetch_assoc();
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
    global $usrStmt;
    //If the ID is an int, then it isn't injection
    if (!is_int(( int )$uID)) {
        return false;
    }
    $usrStmt->bind_param("i", $uID);
    $usrStmt->execute();
    $result = $usrStmt->get_result()->fetch_assoc();
    return count($result) > 0;
}

function getUser($uID) {
    global $usrStmt;
    $usrStmt->bind_param("i", $uID);
    $usrStmt->execute();
    return $usrStmt->get_result()->fetch_assoc();
}

function getUsers() {
    global $allUsrStmt;
    $allUsrStmt->execute();
    return $allUsrStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

?>