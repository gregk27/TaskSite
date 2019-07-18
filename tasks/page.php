<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Usbwebserver</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="/style.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body>
<?php
include $_SERVER ['DOCUMENT_ROOT'] . "/header.php";

include $_SERVER ['DOCUMENT_ROOT'] . "/passwords.php";

$conn = new mysqli ($dbAddress, $dbUser, $dbPass);

$stmt = $conn->prepare ("SELECT * FROM `tasks`.`tasks` WHERE `ID` = ?");
$stmt->bind_param ("i", $_GET ["task"]);
$stmt->execute ();
$task = $stmt->get_result ()->fetch_assoc ();
$stmt->close ();

$task ["subteams"] = explode (",", $task ["subteams"]);
$task ["heads"] = explode (",", $task ["heads"]);
$task ["contributors"] = explode (",", $task ["contributors"]);
$task ["followers"] = explode (",", $task ["followers"]);
$task ["joined"] = false;
$task ["following"] = false;
$task ["head"] = false;

$stmt = $conn->prepare ("SELECT * FROM `tasks`.`subtasks` WHERE `parent`  = ?");
$stmt->bind_param ("i", $_GET ["task"]);
$stmt->execute ();
$subtasks = $stmt->get_result ()->fetch_all (MYSQLI_ASSOC);
$stmt->close ();

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

$level = 0;
if (ISSET ($_GET ["lv"])) {
	$level = $_GET ["lv"];
}
$taskID = -1;
if (ISSET ($_GET["task"])){
	$taskID = $_GET["task"];
}


$stmt = $conn->prepare ("SELECT * FROM `tasks`.`topics` WHERE `taskID` = ? AND 'level' = ?");
$stmt->bind_param ("ii", $taskID, $level);
$stmt->execute ();
$topics = $stmt->get_result ()->fetch_all (MYSQLI_ASSOC);
$stmt->close ();
?>
	<div class="task-page-top" id="top">
		<div class="buttons" style="display:<?php echo $task["head"]? "block":"none"?>">
			<button id="change">-10</button>
			<button id="change">-5</button>
			&nbsp&nbsp
			<button id="change">+5</button>
			<button id="change">+10</button>
		</div>
		<h2><?php echo $task["name"]?></h2>
		<div class="progress">50%</div>
	</div>
	<div id="below-top">
		<div class="task-page-content">
			<div class="description">
				<h2>About</h2>
				<?php echo $task["description"]?>
			</div>
			<div>
				<nav> <a href="?task=<?php echo $task["ID"]?>&lv=0"
					class="<?php echo $level == 0 ? 'underline' : ''?>">Announcements</a>
				<a href="?task=<?php echo $task["ID"]?>&lv=1"
					class="<?php echo $level == 1 ? 'underline' : ''?>">Progress</a> <a
					href="?task=<?php echo $task["ID"]?>&lv=2"
					class="<?php echo $level == 2 ? 'underline' : ''?>">Discussion</a></nav>
				<div id="messages">
				<?php
				
				foreach ( $topics as $topic ) {
					include ("topic.php");
				}
				?>
				</div>
			</div>
	<?php
	for($i = 0; $i < 100; $i ++) {
		echo "<br/>";
	}
	?>
		</div>
	</div>

	<div class="task-page-sidebar" id="sidebar">
		<h3>Subtasks</h3>
		<table>
			<?php
			foreach ( $subtasks as $sub ) {
				echo '<tr id="task">
					<td id="name">' . $sub ["name"] . '</td>
					<td id="percent">' . $sub ["progress"] . '%</td>
				</tr>';
				if (preg_match ("/\|" . $ID . "\b/", $sub ["heads"])) {
					echo '<tr>
						<td id="config" colspan="2">
							<button id="change">-10</button>
							<button id="change">-5</button> &nbsp&nbsp
							<button id="change">+5</button>
							<button id="change">+10</button>
						</td>
					</tr>';
				}
			}
			?>
		</table>
		<div
			style="width: 100%; background-color: black; height: 2px; margin-top: 5px; margin-bottom: 5px"></div>
		<h3>People</h3>
		<strong>Heads</strong>
		<ul>
			<?php
			
foreach ( $task ["heads"] as $head ) {
				echo "<li>" . explode ("|", $head) [0] . "</li>";
			}
			?>
		</ul>
		<strong>Contributors</strong>
		<ul>
			<?php
			
foreach ( $task ["contributors"] as $cont ) {
				echo "<li>" . explode ("|", $cont) [0] . "</li>";
			}
			?>
		</ul>
	</div>
	</div>
</body>
</html>

<script>
//Get the header
var head = document.getElementById("top");
var subs = document.getElementById("sidebar");
var main = document.getElementsByClassName("task-page-content")[0];
var body = document.getElementById("below-top");

//Get the offset position of the navbar
var pos = head.offsetTop;
var subPos = subs.offsetTop;
var subPosX = subs.offsetLeft;
//Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
function sticky() {
// 	main.offsetTop = 244;
	console.log(window.pageYOffset+"\t"+main.offsetTop);
	if (window.pageYOffset > pos) {
		body.style["padding-top"]= "75px";
		head.classList.add("sticky");
	} else {
		head.classList.remove("sticky");
		body.style["padding-top"]= "0px";
	}
	if (window.pageYOffset > subPos-45) {
		subs.style.left = subPosX+"px";
		subs.classList.add("sticky");
	} else {
		subs.classList.remove("sticky");
	}
}

function resize(){
	console.log(subPosX);
	subs.classList.remove("sticky");
	subPosX = subs.offsetLeft;
	console.log(subPosX);
	sticky();
}

document.onscroll = sticky;
window.addEventListener("resize", resize);

function setVal(url, param, value){
	console.log(url);
	console.log(url.indexOf(param));	
	console.log(param+"="+value);	
	if (url.indexOf('?') < 0){
	   url += '?'+param+'='+value;
	}else if (url.indexOf(param) > 0){
	   url = url.replace(new RegExp("("+param+"=[^&\s]*)", 'g'), param+"="+value);
	}else{
	   url += '&'+param+'='+value;
	}

	console.log(url);
	return url;
}

function load(){
	var links = document.getElementsByClassName("pointer");
	for(var i=0; i<links.length; i++){
		links[i].href = setVal(window.location.href, "focus", links[i].id);
	}
	
	
	if(document.getElementById("scrollto") != null){
		document.getElementById("scrollto").scrollIntoView();
		window.scrollBy(0,-100);
	}
}

window.onload = load;
</script>