<?php
include_once("include.php");
    $stmt = $conn->prepare("DELETE FROM tasks.tokens WHERE value = ? AND ip = ?");
    $ip = getIP();
    $stmt->bind_param("is", $_COOKIE["token"], $ip);
    $stmt->execute();
    echo "Deleted entry";
    if(isset($_COOKIE["token"])){
        setcookie("token", -1, time()-3600);
        echo "Deleted token";
    }
?>