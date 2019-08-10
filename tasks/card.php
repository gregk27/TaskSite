<script>

    function showPeople(button) {
        var hidden = button.parentNode.querySelectorAll("#contributors")[0];

        if (hidden.style.visibility == "hidden") {
            hidden.style.visibility = "visible";
        } else {
            hidden.style.visibility = "hidden";
        }

    }

    function sendRequest(id, mode) {
        xhttp.open("POST", "/tasks/backend/join.php", false);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("task=" + id + "&mode=" + mode);

        console.log(xhttp.responseText);

        xhttp.open("GET", window.location.href, false);
        xhttp.send();
        let text = xhttp.responseText;
        // console.log(text);
        let doc = new DOMParser().parseFromString(text, "text/html");
        console.log(doc);
        console.log(document);
        // console.log(topic);
        console.log("tsk" + id);
        let msg = doc.getElementById("tsk" + id);
        let page = document.getElementById("tsk" + id);
        console.log(msg);
        console.log(page);
        page.innerHTML = msg.innerHTML;
        setSize(page);

    }

    function setSize(element) {
        people = element.children[3];
        desc = element.children[2];

        console.log(people.clientHeight);
        desc.style.marginBottom = people.clientHeight + "px";
    }

</script>
<?php

/*
 * $task = array (
 * "name" => "Test task",
 * "progress" => 75,
 * "subteams" => array (
 * "Mech",
 * "Prog"
 * ),
 * "desc" => "We choose to go to the moon. We choose to go to the moon in this decade and do the other things, not because they are easy, but because they are hard, because that goal will serve to organize and measure the best of our energies and skills, because that challenge is one that we are willing to accept, one we are unwilling to postpone, and one which we intend to win, and the others, too.",
 * "subtasks" => array (
 * array (
 * "name" => "Sub 1",
 * "progress" => "50"
 * ),
 * array (
 * "name" => "Sub 2",
 * "progress" => "75"
 * ),
 * array (
 * "name" => "Sub 3",
 * "progress" => "25"
 * )
 * ),
 * "heads" => array (
 * "Greg",
 * "Another Genius"
 * ),
 * "contibutors" => array (
 * "Rookie 1",
 * "Rookie 2",
 * "Rookie 3",
 * "Rookie 4"
 * ),
 * "joined"=>false,
 * "following"=>true
 * );
 */
/*
 * Required variables from including file:
 * $task, contains:
 * ID
 * name
 * subteams (array)
 * progress
 * subtasks (JSON):
 * -name, progress
 * list of heads
 * list of contributors
 * boolean joined
 * boolean following
 */

//old gradient colour: #33cc33;

// foreach($task as $key=>$value){
// echo $key."\t".$value."<br/>";
// }

$stmt = $conn->prepare("SELECT * FROM tasks.tasks WHERE parent = ?");
$stmt->bind_param("i", $task ["ID"]);
$stmt->execute();
$subs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$task ["subteams"] = array_filter(explode(",", $task ["subteams"]));
$task ["subtasks"] = $subs; // json_decode ( $task ["subtasks"], true );
$task ["heads"] = array_filter(explode(",", $task ["heads"]));
$task ["contributors"] = array_filter(explode(",", $task ["contributors"]));
$task ["followers"] = array_filter(explode(",", $task ["followers"]));
$task ["joined"] = false;
$task ["following"] = false;
$task ["head"] = false;

// If the users is a head, they are involved
if (inList(USER["ID"], $task["heads"])) {
    $task ["head"] = true;
    $task ["joined"] = true;
}
if (inList(USER["ID"], $task["contributors"])) {
    $task ["joined"] = true;
}
if (inList(USER["ID"], $task["followers"])) {
    $task ["following"] = true;
}

$title = $task["name"];
if (isset($fullTitle) && $fullTitle) {
    $title = fullPath($task["ID"], isset($stopAt) ? $stopAt : -1);
}

?>

<div class="task-small" id="tsk<?php echo $task["ID"] ?>">
    <div class="top">
        <div class="task-name">
            <?php echo $title ?>
        </div>
        <?php
        $head = $task["headsWanted"];
        $help = $task["helpWanted"];
        if($head || $help) {
            echo "<div class='wanted'>";
            if($head) echo "Heads";
            if ($head && $help) echo "/";
            if($help) echo "Help";
            echo " Wanted</div>";
        }
        ?>
        <div class="progress" id="progress"
             style="<?php echo "background-image:" . createProgressGradient($task) ?> ">
			<span id="percent"> <?php echo round($task["progress"]) . "%" ?>&nbsp&nbsp
		</span><br/> <span id="detail">Subteam<?php echo count($task["subteams"]) > 1 ? "s: " : ": ";
                $out = "";
                foreach ($task["subteams"] as $s) {
                    if (count($task["subteams"]) < 2) {
                        $out = $out . SUBTEAMS[$s]["name"];
                    } else {
                        $out = $out . SUBTEAMS[$s]["short"];
                    }

                    $out = $out . "/";
                }
                echo rtrim($out, "/");
                ?>&nbsp&nbsp
		</span>
        </div>
    </div>
    <div id="sub">
        <strong><a class='plain' href='/tasks/<?php echo $task["ID"] ?>/subtasks'>Subtasks</a></strong>
        <table>
            <?php
            foreach ($task ["subtasks"] as $sub) {
                echo "<tr><td class='hover'>" . $sub ["description"] . "</td><td><a class='plain' href='/tasks/" . $sub["ID"] . "'>" . $sub ["name"] . "</a></td><td>" . $sub ["progress"] . "%</td></tr>";
            }
            ?>
        </table>
        <div id="buttons">
            <?php if (!VALID) {
            } else if ($task["head"]) {
                newButton("console.log('TODO')", true, "Locked for heads", false, "width:100%");
            } else {
                newButton("sendRequest(" . $task["ID"] . ", 'contribute')", !$task["joined"], $task["joined"] ? "Quit" : "Join", true, "width: 27%; float: left; margin-left:2%");
                newButton("sendRequest(" . $task["ID"] . ", 'follow')", !$task["following"], $task["following"] ? "Unfollow" : "Follow", true, "width:40%; float: right;");
            } ?>
        </div>
    </div>

    <div id="desc"><?php echo $task["description"] ?></div>


    <div id="people">
        <span id="heads"> <strong>Head:</strong> <?php foreach ($task["heads"] as $value) {
                printName($value);
                echo ",";
            } ?></span>
        <span id="show-contributors"
              onclick="showPeople(this)"> <?php echo count($task["contributors"]) ?> Contributors </span>
        <br/>
        <span id="contributors"><?php foreach ($task["contributors"] as $value) {
                printName($value);
                echo ",";
            } ?></span>
    </div>

    <script>
        self = document.getElementsByTagName("script");
        self = self[self.length - 1];

        setSize(self.parentElement);

    </script>


</div>
