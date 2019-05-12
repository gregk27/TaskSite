<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Usbwebserver</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="../style.css" rel="stylesheet" type="text/css"
	media="screen" />
</head>
<body>

<?php
$err = "";
if (isset($_POST["mode"])) {
	include "../passwords.php";
	$conn = new mysqli($dbAddress, $dbUser, $dbPass);
    if ($conn->connect_error) {
        die("Connection failed" . $conn->connect_error);
    }
    if ($_POST["mode"] == 'register') {
        // If the user is trying to register
        // Check for username conflicts
        $conflicts = mysqli_num_rows($conn->query("SELECT * FROM `tasks`.`users` WHERE `username`=\"" . $_POST['username'] . "\""));
        echo "SELECT * FROM `tasks`.`users` WHERE `username`=\"" . $_POST['username'] . "\"";
        echo $conflicts;
        if ($conflicts == 0) {
            // Loop until we get a unique ID
            while (true) {
                $num = mt_rand(0, 25400);
                $conflicts = mysqli_num_rows($conn->query("SELECT * FROM `tasks`.`users` WHERE `ID`=" . $num));
                if ($conflicts == 0) {
                    break;
                }
            }
            // Add the user to the database
            $conn->query("INSERT INTO `tasks`.`users` (`username`, `password`, `ID`) VALUES ('" . $_POST['username'] . "','" . $_POST['password'] . "','" . $num . "')");
            // Set the cookie
            echo setcookie("token", $num, time() + 12000000, "/");
        } else {
            $err = "Username taken. Contact Greg if someone else has your name.";
        }
    } else if ($_POST["mode"] == "login") {
        // If the user is signing in
        // Get users with same name/pass
        $users = $conn->query("SELECT `ID` FROM `tasks`.`users` WHERE `username`=\"" . $_POST['username'] . "\" AND `password`=\"" . $_POST['password'] . "\"");
        if (mysqli_num_rows($users) == 1) {
            // Set a cookie based on result
            setcookie("token", $users->fetch_assoc()["ID"], time() + 12000000, "/");
        }
    } else {
        $err = "Mode failed";
    }
    $conn->close();
}
?>


<?php
include "../header.php"?>

<?php

if ($err != "") {
    $err = "<br/></br>" . $err;
}

if (! isset($_COOKIE["token"])) {
    include "login.html";
} else {
    include "settings.php";
}

?>

</body>