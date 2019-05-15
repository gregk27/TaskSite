<?php

include "../passwords.php";


$conn = new mysqli($dbAddress, $dbUser, $dbPass);

$results = $conn->query("SELECT * FROM `tasks`.`tasks` WHERE `ID` = 1")->fetch_assoc();

echo "ID:".$results["ID"]."<br/>";
echo "Name:".$results["name"]."<br/>";
echo "Progress:".$results["progress"]."<br/>";
echo "Teams:".explode(",",$results["subteams"])[0]."<br/>";
echo "Task:".$results["subtasks"]."<br/>";

echo "<table>";
foreach ( json_decode($results["subtasks"], true) as $value ) {
	echo "<tr><td>" . $value ["name"] . "</td><td>" . $value ["progress"] . "%</td></tr>";
}
echo "</table>";

?>