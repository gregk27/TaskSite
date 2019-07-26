<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Usbwebserver</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="/style.css" rel="stylesheet" type="text/css"
	media="screen" />
</head>
<body>

<?php
require_once ($_SERVER['DOCUMENT_ROOT']."/include.php");
$err = "";
if (isset($_POST["mode"])) {
	$conn = new mysqli($dbAddress, $dbUser, $dbPass);
    if ($conn->connect_error) {
        die("Connection failed" . $conn->connect_error);
    }
    if ($_POST["mode"] == 'register') {
        // If the user is trying to register
        // Check for name conflicts
		$stmt = $conn->prepare("SELECT `name` FROM `tasks`.`users` WHERE `name`=?");
		$stmt->bind_param("s", $_POST["name"]);
		$stmt->execute();
		
        $conflicts = mysqli_num_rows($stmt->get_result());
		//If the name isn't taken
        if ($conflicts == 0) {
            // Loop until we get a unique ID
			$stmt = $conn->prepare("SELECT `name` FROM `tasks`.`users` WHERE `ID`=?");
			$stmt->bind_param("i", $num);
            while (true) {
                $num = mt_rand(0, 25400);
				$stmt->execute();
		
				$conflicts = mysqli_num_rows($stmt->get_result());
                if ($conflicts == 0) {
                    break;
                }
            }
            // Add the user to the database
            $stmt = $conn->prepare("INSERT INTO `tasks`.`users` (`name`, `password`, `ID`) VALUES (?, ?, ?)");
			$stmt->bind_param("ssi", $_POST['name'], $_POST['password'], $num);
			$stmt->execute();
            // Set the cookie
			echo setcookie("token", $num, time() + 12000000, "/");
			header("Location: /index.php");
			exit();
        } else {
            $err = "name taken. Contact Greg if someone else has your name.";
        }
    } else if ($_POST["mode"] == "login") {
        // If the user is signing in
        // Get users with same name/pass
        $stmt = $conn->prepare("SELECT `ID` FROM `tasks`.`users` WHERE `name`= ? AND `password`= ?");
		$stmt->bind_param("ss", $_POST['name'], $_POST['password']);
		$stmt->execute();
		$users = $stmt->get_result();
        if (mysqli_num_rows($users) == 1) {
            // Set a cookie based on result
            setcookie("token", $users->fetch_assoc()["ID"], time() + 12000000, "/");
            header("Location: /index.php");
            exit();
        } else{
			$err = "Invalid name/password";
		}
    } else {
        $err = "Mode failed";
    }
    $conn->close();
}
?>


<?php
include $_SERVER['DOCUMENT_ROOT']."/header.php"?>

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