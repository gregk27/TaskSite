<!--Include the main CSS file-->
<link href="/style.css" rel="stylesheet" type="text/css" media="screen"/>

<?php
include_once("passwords.php");

$conn = new mysqli ($dbAddress, $dbUser, $dbPass);
// TODO: Remove after debugging
mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);

$usrStmt = $conn->prepare("SELECT name,ID,email,rookie FROM tasks.users WHERE ID = ?");
$allUsrStmt = $conn->prepare("SELECT name,ID FROM tasks.users WHERE NOT ID=-1");
$permsStmt = $conn->prepare("SELECT heads,contributors FROM tasks.tasks WHERE ID = ?");
$getTask = $conn->prepare("SELECT * FROM tasks.tasks WHERE ID = ?");

define("SUBTEAMS", $conn->query("SELECT * FROM tasks.subteams")->fetch_all(MYSQLI_ASSOC));

if (ISSET($_COOKIE["token"])) {
    $stmt = $conn->prepare("SELECT user FROM tasks.tokens WHERE value = ? AND ip = ?");
    $ip = getIP();
    $stmt->bind_param("is", $_COOKIE["token"], $ip);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    if (isset($u["user"])) {
        define("USER", getUser($u["user"]));
        define("VALID", 1);
    } else {
        //If the users doesn't exist, then delete the token to prevent issues
        setcookie("token", "", time() - 3600);
        header("Refresh:0");
        define("USER", getUser(-1));
        define("VALID", 0);
    }
} else {
    define("USER", getUser(-1));
    define("VALID", 0);
}

function getIP(){
    //TODO: Improve
    return $_SERVER["REMOTE_ADDR"];
}


// TaskID: The ID of the parent task
// level: 0 for head, 1 for contributor, 2 for registered users
function hasPerms($taskID, $level, $uID) {
    global $permsStmt;

    // If the taskID is an integer value, than it's not a risk for injection
    if (!is_int(( int )$taskID)) {
        echo "ERROR: Page Error";
        return false;
    }

    // TOOD: Check if the userID is registered
    if ($level >= 2 && isUser($uID)) {
        return true;
    }

    $permsStmt->bind_param("i", $taskID);
    $permsStmt->execute();
    $users = $permsStmt->get_result()->fetch_assoc();
    $isHead = inList($uID, $users ['heads']);
    $isCont = inList($uID, $users ["contributors"]);

    if ($level == 0 && $isHead)
        return true;
    else if ($level == 1 && ($isHead || $isCont))
        return true;

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

//Get the names and IDs of all users, excluding the NULL user
function getUsers() {
    global $allUsrStmt;
    $allUsrStmt->execute();
    $result = $allUsrStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $out = array();
    foreach ($result as $r) {
        $out[$r["name"]] = $r["ID"];
    }
    return $out;
}

//Cleans a string for places where no HTML is desired
function cleanString($in) {
    //Strip all tags
    $out = strip_tags($in, "");

    //Replace on* event handlers
    $out = preg_replace("/on\w*=([\\\"']).*[^\\\]\\1/sU", "", $out);

    //Strip risky chars
    $out = htmlspecialchars($out);

    return $out;
}

//Formats a string for places where HTML is accepted
function formatString($in) {
    //Replace newlines//Replace newlines
    $out = $in;
    while(strpos($out, "\n") !== false){
        $out = preg_replace("/(>[\w\s]*)\n([\w\s]*<)/s", "$1<br/>$2", ">".$out."<");
        $out = ltrim(rtrim($out, "<"), ">");
    }
    //Replace on* event handlers
    $out = preg_replace("/on\w*=([\\\"']).*[^\\\]\\1/sU", "", $out);

    //Close open tags
    $doc = new DOMDocument();
    $doc->loadHTML($out);
    preg_match("/<body>(.*)<\/body>/s", $doc->saveHTML(), $res);
    $out = $res[1];

    //Escape scripts
//    $out = preg_replace("/<(.?)script>/", "&lt;$1script&gt;", $out);

    //Strip most tags, allowing basic formatting
    $out = strip_tags($out, "<strong><ul><li><span><h3><a><img><br><i><b><div>");

    //Remove IDs
    $out = preg_replace("/ id=([\"'])\S*\\1([ >])/", "\\2", $out);

    return $out;
}

function inList($check, $list) {
    //We need a string for the regex to work
    if (is_array($list))
        $list = implode(",", $list);
    return preg_match("/\b" . $check . "\b/", $list);
}

function printName($ID, $print = true) {
    $name = getUser($ID)["name"];
    //TODO:Update href when users pages are created
    $out = "<a class='plain' href='users.php?n=" . $name . "'>" . $name . "</a>";
    if ($print) echo $out;
    return $out;
}

function fullPath($ID, $stopAt = -1) {
    global $conn, $getTask;
    $getTask->bind_param("i", $ID);
    $getTask->execute();
    $task = $getTask->get_result()->fetch_assoc();
    // Create title
    $title = "<a class='button active' href='/tasks/" . $ID . "'>".$task["name"]."</a>";
    $stmt = $conn->prepare("SELECT name,parent FROM tasks.tasks WHERE ID = ?");
    $val = $task ["parent"];
    $res = "temp";
    $stmt->bind_param("i", $val);
    $stmt->bind_result($res, $val);
    // Iterate over parents until top-level is found
    while ($val != $stopAt) {
        $tempID = $val;
        $stmt->execute();
        $stmt->fetch();
        $title = "<a class='button active' href='/tasks/" . $tempID . "'>" . $res . "</a>>" . $title;
    }
    $stmt->close();
    return $title;
}

function newButton($onclick, $active, $content, $enabled = true, $style = null, $ID = null, $echo = true) {
    if($content == "" && !$enabled){
        $style .= "display:none";
    }
    $out = "<button "; //Open tag
    $out .= 'onclick = "' . str_replace("\"", "\\\"", $onclick) . '" ';
    $out .= $ID == null ? "" : "id=\"" . $ID . "\" ";
    $out .= 'class="button ' . ($active ? "" : "de") . 'active" ';
    $out .= $style == null ? "" : "style=\"" . $style . "\" ";
    $out .= $enabled ? "" : "disabled";
    $out .= ">" . $content;
    $out .= "</button>";

//    if(!$enabled) $out = "";

    if ($echo)
        echo $out;

    return $out;
}

function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
    {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}

function isGet($name, $val){
    return isset($_GET[$name]) && $_GET[$name]==$val;
}

function createProgressGradient($task){
    $gradient = "linear-gradient(120deg, ";
    $gradient .= "green " . ($task["progress"] - 2) . "%, ";
    if ($task["unassigned"] != $task["local"]) {
        $gradient .= "#909090 " . ($task["progress"] + 2) . "%, ";
        $gradient .= "#909090 " . ($task["progress"] + $task["unassigned"] - $task["local"] - 2) . "%, ";
        $gradient .= "#797979 " . ($task["progress"] + $task["unassigned"] - $task["local"] + 2) . "%, ";
    } else {
        $gradient .= "#797979 " . ($task["progress"] + 2) . "%, ";
    }

    return rtrim($gradient, ", ") . ")";
}

function getTask($ID){
    global $getTask;
    $getTask->bind_param("i", $ID);
    $getTask->execute();
    return $getTask->get_result()->fetch_assoc();
}

?>

<script>
    var xhttp = new XMLHttpRequest();
</script>
