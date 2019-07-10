
<link href="/style.css" rel="stylesheet" type="text/css" media="screen" />
<?php
include $_SERVER ['DOCUMENT_ROOT'] . "/passwords.php";

$conn = new mysqli ($dbAddress, $dbUser, $dbPass);

$topic = array (
		"title" => "Test",
		"name" => " Greg",
		"time" => time (),
		"up" => 7,
		"down" => 4,
		"text" => "Sed ut perspiciatis unde omnis iste natus error sit
			voluptatem accusantium doloremque laudantium, totam rem aperiam,
			eaque ipsa quae ab illo inventore veritatis et quasi architecto
			beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia
			voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur
			magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro
			quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur,
			adipisci velit, sed quia non numquam eius modi tempora incidunt ut
			labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima
			veniam, quis nostrum exercitationem ullam corporis suscipit
			laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel
			eum iure reprehenderit qui in ea voluptate velit esse quam nihil
			molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas
			nulla pariatur? nihil molestiae consequatur, vel illum qui dolorem
			eum fugiat quo voluptas nulla",
		"replies" => array (33,197,330)
);

$format = "M d/y | h:ia";
$stamp = new DateTime ("@" . $topic ["time"]);
date_timezone_set ($stamp, new DateTimeZone ("EST"));
$date = date_format ($stamp, $format);

?>


<script>
	function showReplies(element){
		var target = element.parentElement.parentElement.querySelector("#replies");
		if(target.style.display == "block"){
			target.style.display = "none";
		}
		else if(target.style.display == "none"){
			target.style.display = "block";
		}
	}
</script>
<div class="task-page-content">
	<div class="message">
		<div id="about">
			<h3 id="title"><?php echo $topic["title"]?></h3>
			<h5 id="info"><?php echo $topic["name"]?><span
					style="padding-left: 25px"><?php echo $date?></span>
			</h5>
		</div>
		<div id="vote">
			<button>Yea</button>
			<span id="score"><?php echo $topic["up"]."|".$topic["down"]?></span>
			<button>Nay</button>
		</div>
		<div id="content"><?php echo $topic["text"]?></div>
		<div id="show-comments">
			<a onclick="showReplies(this);">Show <?php echo count($topic["replies"])?> comments</a>
		</div>
		<div id="replies" style="display: none">
			<div class="reply">
				<h4 id="info">
					<div style="position: relative; top: 20px">Greg 01/01/01</div>
					<div id="bar" />
					<div id="vote">
						<button>Yea</button>
						<span id="score">7|5</span>
						<button>Nay</button>
					</div>
				</h4>
				Vestibulum nulla ex, ultricies id commodo at, tempus vel mi.
				Suspendisse tempor lorem ipsum, quis cursus magna euismod in. Morbi
				interdum risus a orci molestie, in ultricies dolor efficitur.
				Maecenas nulla augue, aliquam sit amet mollis eget, pellentesque et
				sapien. Cras varius blandit tempus.
			</div>
		</div>
	</div>
</div>
