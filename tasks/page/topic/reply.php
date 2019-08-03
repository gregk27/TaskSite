<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include.php");
$format = "M d/y | h:ia";
$stamp = new DateTime ("@" . $reply ["time"]);
date_timezone_set($stamp, new DateTimeZone ("EST"));
$date = date_format($stamp, $format);

$voteUp = inList(USER["ID"], $reply ["up"]);
$voteDown = inList(USER["ID"], $reply ["down"]);

?>

<div class="reply" id="<?php echo "r" . $reply["ID"] ?>">
    <h4 id="info">
        <div style="position: relative; top: 17px">
            <a><?php echo getUser($reply["user"])["name"] ?></a><span
                    style="padding-left: 25px; font-size: 75%"><?php echo $date ?></span>
            <a class="pointer" style="font-size: 75%"
               id="<?php echo "r" . $reply["ID"] ?>"><?php echo "#r" . $reply["ID"] ?></a>
        </div>
        <div id="bar"/>
        <div id="vote">
            <?php newButton("vote('reply'," . $reply['ID'] . ", 'up')", !$voteUp, "Yea", VALID) ?>
            <span id="score"><?php echo count(array_filter(explode(",", $reply["up"]))) . "|" . count(array_filter(explode(",", $reply["down"]))) ?></span>
            <?php newButton("vote('reply'," . $reply['ID'] . ", 'down')", !$voteDown, "Nay", VALID) ?>
        </div>
    </h4>
    <p>
        <?php echo $reply["text"] ?>
    </p>
</div>