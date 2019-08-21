<div class="task-page sidebar" id="sidebar">
    <?php if (!VALID) echo "<!--"; ?>
    <div id="buttons" style="margin-left:-15px; margin-right:-35px;">
        <?php if ($task["head"]) {
            echo "<a class='button active' href='options' style='width: 70%;'>Options</a>";
        } else {
            newButton("sendRequest('contribute')", !$task["joined"], $task["joined"] ? "Quit" : "Join", true, "width: 27%; float: left;");
            newButton("sendRequest('follow')", !$task["following"], $task["following"] ? "Unfollow" : "Follow", true, "width:40%; float: right;");
        } ?>
    </div>
    <?php if (!VALID) echo "-->"; ?>

    <h3><a class="plain" href="subtasks">Subtasks</a></h3>
    <table style="font-size:inherit">
        <?php
        if (count($subtasks) == 0) {
            echo "<tr><td>No subtasks</td></tr>";
        }
        foreach ($subtasks as $sub) {
            echo '<tr id="task">
					<td id="name"><a class="plain" href="/tasks/'.$sub["ID"].'">' . $sub ["name"] . '</a></td>
					<td id="percent">' . round($sub ["progress"]) . '%</td>
				</tr>';
            if (inList(USER["ID"], $sub ["heads"])) {
                $max = $sub["unassigned"] == $sub["local"];
                $min = $sub["local"] == 0;
                echo '<tr>
						<td id="config" colspan="2">';
                if ($min) newButton("", false, "Min hit", false, "font-size:15px; padding:4px 10px;");
                else {
                    newButton("setProgress(" . $sub["ID"] . ", -5, 'sidebar,name,tsk')", true, "-5", true, null, "change");
                    newButton("setProgress(" . $sub["ID"] . ", -1, 'sidebar,name,tsk')", true, "-1", true, null, "change");
                }
                echo '&nbsp&nbsp';
                if ($max) newButton("", false, "Max hit", false, "font-size:15px; padding:4px 10px;");
                else {
                    newButton("setProgress(" . $sub["ID"] . ", 1, 'sidebar,name,tsk')", true, "+1", true, null, "change");
                    newButton("setProgress(" . $sub["ID"] . ", 5, 'sidebar,name,tsk')", true, "+5", true, null, "change");
                }
                echo '</td>
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

        foreach ($task ["heads"] as $head) {
            echo "<li>" . printName($head, false) . "</li>";
        }
        ?>
    </ul>
    <strong>Contributors</strong>
    <ul>
        <?php

        foreach ($task ["contributors"] as $cont) {
            echo "<li>" . printName($cont, false) . "</li>";
        }
        ?>
    </ul>
</div>