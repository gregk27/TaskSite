<?php
include $_SERVER ['DOCUMENT_ROOT'] . "/passwords.php";
$conn = new mysqli ( $dbAddress, $dbUser, $dbPass );

$ID = 254; // $_COOKIE["token"];

$taskID = 1; // $_POST["task"];
$mode = "contribute"; // $_POST["mode"]; //"contribute" or "follow"

$stmt = $conn->prepare ( "SELECT `heads`,`contributors`,`followers` FROM `tasks`.`tasks` WHERE `ID` = ?" );
$stmt->bind_param ( "i", $taskID );
$stmt->execute ();
$result = $stmt->get_result ()->fetch_assoc ();

if ($mode == "contribute") {
	echo $result ["contributors"] . "<br/>";

	$found = false;
	$array = explode ( ",", $result ["contributors"] );
	foreach ( $array as $key => $value ) {
		if (explode ( "|", $value ) [1] == $ID) {
			unset ( $array [$key] );
			$found = true;
		}
	}
	array_values ( $array );
	if (!$found) {
		$stmt2 = $conn->prepare ( "SELECT `username` FROM `tasks`.`users` WHERE `ID`=?" );
		$stmt2->bind_param ( "i", $ID );
		$stmt2->execute ();
		$result2 = $stmt2->get_result ()->fetch_assoc ()["username"];
		
		array_push($array, $result2."|".$ID);
	}
	echo implode ( ",", $array );
}

if ($mode == "follow") {
	echo $result ["following"] . "<br/>";
	
	$found = false;
	$array = explode ( ",", $result ["following"] );
	foreach ( $array as $key => $value ) {
		if ($value == $ID) {
			unset ( $array [$key] );
			$found = true;
		}
	}
	array_values ( $array );
	if (!$found) {
		array_push($array, $ID);
	}
	echo implode ( ",", $array );
}

?>