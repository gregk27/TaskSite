<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Usbwebserver</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
</head>
<body id="task-page">
<?php
//Ensure that level is set, if not the default is announcements
if (!isset($_GET["lv"])) {
        header("Location: " . rtrim($_SERVER["REQUEST_URI"], "/") . "/ann");
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/header.php");

include ("components/scripts.php");
?>


<?php include("components/top.php") ?>
<div class="below-top">

    <?php include("components/sidebar.php") ?>
    <div class="content">
        <div class="section">
            <div style="float:right; font-size:18px; margin-top:7px">
                Subteam<?php echo count($task["subteams"]) > 1 ? "s: " : ": ";
                $out = "";
                foreach ($task["subteams"] as $s) {
                    $out = $out . SUBTEAMS[$s]["name"];

                    $out = $out . "/";
                }
                echo rtrim($out, "/");
                ?>
            </div>
            <h2>About</h2>
            <?php echo $task["description"] ?>
        </div>
        <div class="box">
            <nav><a href="ann" class="<?php echo $level == 0 ? 'underline' : '' ?>">Announcements</a>
                <a href="prog" class="<?php echo $level == 1 ? 'underline' : '' ?>">Progress</a>
                <a href="disc" class="<?php echo $level == 2 ? 'underline' : '' ?>">Discussion</a>
                <a href="chat" class="<?php echo $level == 3 ? 'underline' : '' ?>">Chat</a>
                <?php echo $canPost ? '<a id="interact" class="button active" style="float:right" onclick="showDiag(\'new-topic\')">New</a>' : ''; ?>
            </nav>
            <div id="content">
                <?php
                if ($level == 3) {
                    echo "<div class='error'>Live(ish) chat will be added. Eventually.</div>";
                } else {
                    foreach ($topics as $topic) {
                        include("topic/topic.php");
                    }

                    if (count($topics) == 0) {
                        echo "<div class='error'>No one has posted yet.</div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>

</div>
</body>
</html>

<script>


    function setVal(url, param, value) {
        console.log(url);
        console.log(url.indexOf(param));
        console.log(param + "=" + value);
        if (url.indexOf('?') < 0) {
            url += '?' + param + '=' + value;
        } else if (url.indexOf(param) > 0) {
            url = url.replace(new RegExp("(" + param + "=[^&\s]*)", 'g'), param + "=" + value);
        } else {
            url += '&' + param + '=' + value;
        }

        console.log(url);
        return url;
    }

    function load() {
        var links = document.getElementsByClassName("pointer");
        for (var i = 0; i < links.length; i++) {
            links[i].href = setVal(window.location.href, "focus", links[i].id);
        }


        if (document.getElementsByClassName("scrollto")[0] != null) {
            document.getElementsByClassName("scrollto")[0].scrollIntoView();
            window.scrollBy(0, -100);
        }
    }

    window.onload = load;
</script>
<?php
if ($task["head"]) include "popup/newtask.html";
if ($canPost) {
    include "popup/newtopic.html";
}
?>