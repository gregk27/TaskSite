<?php
require_once ($_SERVER['DOCUMENT_ROOT']."/include.php");

//TODO: Rewrite this page to use a more efficient, secure sytstem

$stmt = $conn->prepare("SELECT `name` FROM `tasks`.`users` WHERE `ID`=?");
$stmt->bind_param("i", $_COOKIE ["token"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc ();
// Setting this to a placeholder, as we don't want to expose password
$user ["password"] = "********";

$sql = "UPDATE `tasks`.`users` SET ";

$changed = false;

foreach ( $_POST as $key => $value ) {
	// If the value has been changed
	if ($value != $user [$key]) {
		$sql = $sql . "`" . $key . "`='" . $value . "', ";
		$changed = true;
	}
}

$sql = rtrim ( $sql, ", " ) . " WHERE `ID`=" . $_COOKIE ["token"];
if($changed){
	$conn->query($sql);
	header("Refresh:0");
}

$conn->close();
?>


<form class="login" onload="init()" style="width: 70%" method="post"
	id="form"></form>
<?php

$fields = "";
$defaults = "";

foreach ( $user as $key => $val ) {
	$fields = $fields . "'" . $key . "',";
	$defaults = $defaults . "'" . $val . "',";
}

// $fields = $fields . "'password'";
// $defaults = $defaults . "'********'";

echo "<script>\nvar fields = [" . $fields . "];\nvar defaults = [" . $defaults . "];\n</script>";
?>
<script>
	function enable(element, item) {
		if (element.parentNode.childNodes[1].disabled) {
			element.parentNode.childNodes[1].disabled = false;
			element.innerHTML = "Undo";
		} else {
			element.parentNode.childNodes[1].disabled = true;
			element.innerHTML = "Edit";
			element.parentNode.childNodes[1].value = defaults[item];
		}

	}

	var width = 0;

	for (var i = 0; i < fields.length; i++) {
		if (fields[i].length > width) {
			width = fields[i].length + 2;
		}
	}

	console.log(width);

	console.log("init")
	var div = document.getElementById("form");

	var content = "<h2>User Settings</h2>"
	for (var i = 0; i < fields.length; i++) {
		var label = fields[i].charAt(0).toUpperCase() + fields[i].slice(1);
		for (var j = label.length; j < width; j++) {
			label += "&nbsp";
		}

		content += "<label>"
				+ label
				+ "<input name='"+fields[i]+"' value='"+defaults[i]+"' disabled='true'/> <button type='button' onclick='enable(this, "
				+ i + ")' style='width:60px'>Edit</button></label><br/><br/>\n"
	}
	content += "<input type='submit' style='margin: auto; display: block;'/>";

	div.innerHTML = content;
	console.log(content);
</script>