<?php
include $_SERVER ['DOCUMENT_ROOT'] . "/passwords.php";
$conn = new mysqli ( $dbAddress, $dbUser, $dbPass );

$ID = 254; // $_COOKIE["token"];

$taskID = 1; // $_POST["task"];
$mode = "follow"; // $_POST["mode"]; //"contribute" or "follow"

$stmt = $conn->prepare ( "SELECT `heads`,`contributors`,`followers` FROM `tasks`.`tasks` WHERE `ID` = ?" );
$stmt->bind_param ( "i", $taskID );
$stmt->execute ();
$result = $stmt->get_result ()->fetch_assoc ();

$out = null;
$col = null;

if ($mode == "contribute") {
	$col = 'contributors';
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
	$out = ltrim(implode ( ",", $array ), ",");
	echo $out;
}

if ($mode == "follow") {
	$col = 'followers';
	echo $result ["followers"] . "<br/>";
	
	$found = false;
	$array = explode ( ",", $result ["followers"] );
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
	$out = ltrim(implode ( ",", $array ), ",");
	echo $out;
}

if($out != null){
	echo isset($col);
	echo "Setting ".$col." to ".$out;
	$stmt = $conn->prepare ( "UPDATE `tasks`.`tasks` SET `".$col."` = ? WHERE `tasks`.`ID` = ?" );
	$stmt->bind_param ( "si", $out, $taskID );
	$stmt->execute ();
}
?>