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

$task ["subteams"] = explode (",", $task ["subteams"]);
$task ["subtasks"] = json_decode ($task ["subtasks"], true);
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
?>
	<script>
		function showReplies(element){
			var target = element.parentElement.parentElement.querySelector("#replies");
			console.log("Element: "+ element)
			console.log("Target: " + target.style.display);
			if(target.style.display == "block"){
				target.style.display = "none";
			}
			else if(target.style.display == "none"){
				target.style.display = "block";
			}
		}
	</script>

	<div class="task-page-top" id="top">
		<div class="buttons">
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
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
					eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim
					ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut
					aliquip ex ea commodo consequat. Duis aute irure dolor in
					reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
					pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
					culpa qui officia deserunt mollit anim id est laborum.</p>
			</div>
			<div>
				<nav> <a class="underline">Announcements</a> <a class="">Progress</a>
				<a>Discussion</a></nav>
				<div id="messages">
					<div class="message">
						<div id="about">
							<h3 id="title">Task started</h3>
							<h5 id="info">Greg 01/01/01</h5>
						</div>
						<div id="vote">
							<button>Yea</button>
							<span id="score">7|5</span>
							<button>Nay</button>
						</div>
						<div id="content">Sed ut perspiciatis unde omnis iste natus error
							sit voluptatem accusantium doloremque laudantium, totam rem
							aperiam, eaque ipsa quae ab illo inventore veritatis et quasi
							architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam
							voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed
							quia consequuntur magni dolores eos qui ratione voluptatem sequi
							nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor
							sit amet, consectetur, adipisci velit, sed quia non numquam eius
							modi tempora incidunt ut labore et dolore magnam aliquam quaerat
							voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem
							ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi
							consequatur? Quis autem vel eum iure reprehenderit qui in ea
							voluptate velit esse quam nihil molestiae consequatur, vel illum
							qui dolorem eum fugiat quo voluptas nulla pariatur? nihil
							molestiae consequatur, vel illum qui dolorem eum fugiat quo
							voluptas nulla</div>
						<div id="show-comments">
							<a onclick="showReplies(this);"><span>Show</span> 5 comments</a>
						</div>
						<div id="replies" style="display: none">
							<div class="reply">
								<div id="data">
									<h5 id="info">
										<div style="position: relative; top: 15px">Greg 01/01/01</div>
										<div id="bar" />
										<div id="vote">
											<button>Yea</button>
											<span id="score">7|5</span>
											<button>Nay</button>
										</div>
									</h5>
								</div>
								Vestibulum nulla ex, ultricies id commodo at, tempus vel mi.
								Suspendisse tempor lorem ipsum, quis cursus magna euismod in.
								Morbi interdum risus a orci molestie, in ultricies dolor
								efficitur. Maecenas nulla augue, aliquam sit amet mollis eget,
								pellentesque et sapien. Cras varius blandit tempus.
							</div>


							<div class="reply">
								<div id="data">
									<h5 id="info">
										<div style="position: relative; top: 15px">Greg 01/01/01</div>
										<div id="bar" />
										<div id="vote">
											<button>Yea</button>
											<span id="score">7|5</span>
											<button>Nay</button>
										</div>
									</h5>
								</div>
								Vestibulum nulla ex, ultricies id commodo at, tempus vel mi.
								Suspendisse tempor lorem ipsum, quis cursus magna euismod in.
								Morbi interdum risus a orci molestie, in ultricies dolor
								efficitur. Maecenas nulla augue, aliquam sit amet mollis eget,
								pellentesque et sapien. Cras varius blandit tempus.
							</div>
						</div>
					</div>
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
			<tr id="task">
				<td id="name">Subtask 1</td>
				<td id="percent">50%</td>
			</tr>
			<tr>
				<td id="config" colspan="2">
					<button id="change">-10</button>
					<button id="change">-5</button> &nbsp&nbsp
					<button id="change">+5</button>
					<button id="change">+10</button>
				</td>
			</tr>
			<tr id="task">
				<td id="name">Subtask 1</td>
				<td id="percent">50%</td>
			</tr>
			<tr>
				<td id="config" colspan="2">
					<button id="change">-10</button>
					<button id="change">-5</button> &nbsp&nbsp
					<button id="change">+5</button>
					<button id="change">+10</button>
				</td>
			</tr>
			<tr id="task">
				<td id="name">Subtask 1</td>
				<td id="percent">50%</td>
			</tr>
			<tr>
				<td id="config" colspan="2">
					<button id="change">-10</button>
					<button id="change">-5</button> &nbsp&nbsp
					<button id="change">+5</button>
					<button id="change">+10</button>
				</td>
			</tr>
		</table>
		<div
			style="width: 100%; background-color: black; height: 2px; margin-top: 5px; margin-bottom: 5px"></div>
		<h3>People</h3>
		<strong>Heads</strong>
		<ul>
			<li>Greg</li>
			<li>Greg (Again)</li>
		</ul>
		<strong>Contributors</strong>
		<ul>
			<li>Rookie 1</li>
			<li>Rookie 2</li>
			<li>Rookie 3</li>
			<li>Rookie 4</li>
			<li>Rookie 5</li>
			<li>Rookie 6</li>
		</ul>
			<?php
			for($i = 0; $i < 15; $i ++) {
				echo "<br/>";
			}
			?>
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
</script>