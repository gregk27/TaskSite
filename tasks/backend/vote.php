<?php
$ID = $_POST ["ID"];
$type = $_POST ["type"]; // topic or reply
$mode = $_POST ["mode"]; // up or down

$user = USER["ID"];

require_once ($_SERVER['DOCUMENT_ROOT']."/include.php");

// Ensure that type is as safe value
echo $type;
if ($type == "topic") {
	$table = "tasks.topics";
} else if ($type == "reply") {
	$table = "tasks.replies";
} else {
	echo "Invalid type: " . type;
	exit (1);
}

$stmt = $conn->prepare ("SELECT up,down FROM " . $table . " WHERE `ID` = ?");
$stmt->bind_param ("i", $ID);
$up = - 1;
$down = - 1;
$stmt->execute ();
$stmt->bind_result ($up, $down);
$stmt->fetch ();

$up = explode (",", $up);
$down = explode (",", $down);

echo count ($up) . "|" . implode (",", $up);
echo "<br/>";
echo count ($down) . "|" . implode (",", $down);

if ($mode == "up") {
	// If it's in, remove it
	if (in_array ($user, $up)) {
		unset ($up [array_search ($user, $up)]);
	} else {
		// Add it
		array_push ($up, $user);
		// Remove it from the other if it's contained
		if (in_array ($user, $down)) {
			unset ($down [array_search ($user, $down)]);
		}
	}
}
if ($mode == "down") {
	// If it's in, remove it
	if (in_array ($user, $down)) {
		unset ($down [array_search ($user, $down)]);
	} else {
		// Add it
		array_push ($down, $user);
		// Remove it from the other if it's contained
		if (in_array ($user, $up)) {
			unset ($up [array_search ($user, $up)]);
		}
	}
}

echo "<br/>";
echo "<br/>";
echo implode (",", $up);
echo "<br/>";
echo implode (",", $down);
echo "<br/>";

$stmt->close();

$outUp = implode (",", $up);
$outDown = implode (",", $down);

echo "UPDATE " . $table . " SET `up` = `".$outUp."`, `down` = `".$outDown."` WHERE `ID` = ".$ID;
$stmt = $conn->prepare ("UPDATE " . $table . " SET `up` = ?, `down` = ? WHERE `ID` = ?");
$stmt->bind_param ("ssi", $outUp, $outDown, $ID);
$stmt->execute ();
?>