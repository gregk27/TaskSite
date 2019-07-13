<?php
$format = "M d/y | h:ia";
$stamp = new DateTime ("@" . $reply ["time"]);
date_timezone_set ($stamp, new DateTimeZone ("EST"));
$date = date_format ($stamp, $format);


$voteUp = in_array($_COOKIE["token"], explode(",", $reply["up"]));
$voteDown = in_array($_COOKIE["token"], explode(",", $reply["down"]));

?>

<div class="reply">
	<h4 id="info">
		<div style="position: relative; top: 17px">
			<?php echo $reply["user"]?><span
				style="padding-left: 25px; font-size: 75%"><?php echo $date?></span>
		</div>
		<div id="bar" />
		<div id="vote">
			<button onclick="vote('reply', <?php echo $reply['ID']?>, 'up')" class = "button <?php if($voteUp) echo "de"?>active">Yea</button>
			<span id="score"><?php echo count(explode(",",$reply["up"]))."|".count(explode(",",$reply["down"]))?></span>
			<button onclick="vote('reply', <?php echo $reply['ID']?>, 'down')" class = "button <?php if($voteDown) echo "de"?>active">Nay</button>
		</div>
	</h4>
	<?php echo $reply["text"]?>
</div>