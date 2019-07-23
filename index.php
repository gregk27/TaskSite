<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Usbwebserver</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="style.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body>
<?php
require_once ($_SERVER['DOCUMENT_ROOT']."/header.php");

$stmt = $conn->prepare ("SELECT * FROM `tasks`.`tasks` WHERE parent = -1");
$stmt->execute ();
$result = $stmt->get_result ()->fetch_all (MYSQLI_ASSOC);
$stmt->close ();

$stmt = $conn->prepare ("SELECT * FROM tasks.tasks WHERE parent = ?");

foreach ( $result as $task ) {
	// foreach($task as $key=>$value){
	// echo $key."\t".$value."<br/>";
	// }
	
	$stmt->bind_param ("i", $task ["ID"]);
	$stmt->execute ();
	$subs = $stmt->get_result ()->fetch_all (MYSQLI_ASSOC);
	
	$task ["subteams"] = explode (",", $task ["subteams"]);
	$task ["subtasks"] = $subs; // json_decode ( $task ["subtasks"], true );
	$task ["heads"] = explode (",", $task ["heads"]);
	$task ["contributors"] = explode (",", $task ["contributors"]);
	$task ["followers"] = explode (",", $task ["followers"]);
	$task ["joined"] = false;
	$task ["following"] = false;
	$task ["head"] = false;
	
	if (isset ($_COOKIE ["token"])) {
		$ID = $_COOKIE ["token"];
		// If the user is a head, they are involved
		foreach ( $task ["heads"] as $value ) {
			if (explode ("|", $value) [1] == $ID) {
				$task ["head"] = true;
			}
		}
		foreach ( $task ["contributors"] as $value ) {
			if (explode ("|", $value) [1] == $ID) {
				$task ["joined"] = true;
			}
		}
		foreach ( $task ["followers"] as $value ) {
			if ($value == $ID) {
				$task ["following"] = true;
			}
		}
	}
	
	include ("tasks/small.php");
}

$stmt->close ();
$conn->close ();

?>
</body>