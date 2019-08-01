<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Usbwebserver</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
</head>


<body>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/header.php");

$stmt = $conn->prepare("SELECT * FROM `tasks`.`tasks` WHERE parent = -1");
$stmt->execute();
$result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT * FROM tasks.tasks WHERE parent = ?");


include("tasks/filterPage.php");

$stmt->close();
$conn->close();

?>

</body>
