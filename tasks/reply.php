<?php
$format = "M d/y | h:ia";
$stamp = new DateTime ("@" . $reply ["time"]);
date_timezone_set ($stamp, new DateTimeZone ("EST"));
$date = date_format ($stamp, $format);
?>

<div class="reply">
	<h4 id="info">
		<div style="position: relative; top: 10px">
			<?php echo $reply["name"]?><span style="padding-left: 25px; font-size:75%"><?php echo $date?></span>
		</div>
		<div id="bar" />
		<div id="vote">
			<button>Yea</button>
			<span id="score"><?php echo $reply["up"]."|".$reply["down"]?></span>
			<button>Nay</button>
		</div>
	</h4>
	<?php echo $reply["text"]?>
</div>