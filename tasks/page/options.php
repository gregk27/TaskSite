<body id="task-page">
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/header.php");

include("components/scripts.php");

if (!$task["head"]) {
    echo "<div class='error'>Nothing to see here. Move along.</div>";
    header("Location: ann");
}
//Check if there is data to work with, and that the user has adequate permissions
if (isset($_POST["name"]) && $task["head"]) {
    $stmt = $conn->prepare("UPDATE tasks.tasks SET name=?, description=?, subteams=? WHERE ID = ?");

    $name = cleanString($_POST["name"]);
    $desc = formatString($_POST["desc"]);
    $sub1 = cleanString($_POST ["team1"]);
    $sub2 = cleanString($_POST ["team2"]);
    $sub3 = cleanString($_POST ["team3"]);

    $subteams = array();
    if ($sub1 != 0) {
        array_push($subteams, $sub1);
    }
    if ($sub2 != 0) {
        array_push($subteams, $sub2);
    }
    if ($sub3 != 0) {
        array_push($subteams, $sub3);
    }

    $subteams = implode(",", $subteams);

    $stmt->bind_param("ssss", $name, $desc, $subteams, $_GET["task"]);
    $stmt->execute();

    //Refresh the task information
    include("components/scripts.php");
}

?>


<style>
    form {
        margin: 15px 50px;
    }

    textarea {
        width: 85%;
        margin: auto;
        display: block;
        resize: none;
    }

    input {
        border: 0px;
        padding: 4px;
    }

    #preview {
        width: 100%;
        min-height: 100px;
        max-height: 300px;
        overflow: scroll;
        margin-top: 15px;
        background-color: #cccccc;
    }

    #subteams {
        width: 100%;
        display: flex;
        flex-direction: row;
        justify-content: space-around;
    }

    #subteams select {
        border: none;
        padding: 3px;
        font-size: 15px;
    }

    .content ul {
        list-style-type: none;
        font-size: 17px;
        margin-top: 0;
    }

    .content ul .button {
        font-size: 70%;
    }

</style>

<?php include("components/top.php") ?>
<div class="below-top">

    <?php include("components/sidebar.php") ?>
    <div class="content">
        <div class="section">
            <form method="post" style="margin:0">
                <h2>General</h2>
                <label>Name <input type="text" name="name" placeholder="The task name"
                                   value="<?php echo $task["name"] ?>"/></label>
                <?php if ($task["parent"] == -1) echo "<!--" ?>
                <label style="margin-left:0px; float:right; width:50%">Weight <input oninput="showValue(this)"
                                                                                     class="slider" type="range"
                                                                                     name="weight"
                                                                                     value="<?php echo $task['unassigned'] / 5 ?>"
                                                                                     min="0"
                                                                                     max="<?php echo $task['unassigned'] ?>"/>
                    <input oninput="showValue(this)" class="purple" id="value" type="text"
                           value="<?php echo $task['unassigned'] / 5 ?>"
                           maxlength="3"/><span
                            class="purple unit">%</span></label>
                <br/>
                <?php if ($task["parent"] == -1) echo "--><br/>" ?>
                <br/>
                <h3 style="width:100%; text-align:center; display:block; margin-bottom: 0px">Subteams</h3>
                <div id="subteams">
                    <?php $subs = $task["subteams"] ?>
                    <select name='team1'>
                        <?php foreach (SUBTEAMS as $team) {
                            $end = $team["ID"] == $subs[0] ? "selected" : "";
                            echo "<option value='" . $team["ID"] . "' " . $end . ">" . $team["name"] . "</option>";
                        } ?>
                    </select>

                    <select name='team2'>
                        <?php foreach (SUBTEAMS as $team) {
                            $end = $team["ID"] == $subs[1] ? "selected" : "";
                            echo "<option value='" . $team["ID"] . "' " . $end . ">" . $team["name"] . "</option>";
                        } ?>
                    </select>

                    <select name='team3'>
                        <?php foreach (SUBTEAMS as $team) {
                            $end = $team["ID"] == $subs[2] ? "selected" : "";
                            echo "<option value='" . $team["ID"] . "' " . $end . ">" . $team["name"] . "</option>";
                        } ?>
                    </select>
                </div>
                <br/>
                <label>
                    Description<br/>
                    <textarea id="description" name="desc" rows="15"
                              oninput="preview(this)"><?php echo preg_replace("/<br\/?>/", "", $task["description"]) ?></textarea>
                </label>

                <div id="preview">
                    <?php echo $task["description"] ?>
                </div>
                <br/>
                <button class="button active">Apply</button>
            </form>
        </div>

        <div class="section">
            <div id="wanted" style="float:right; display:flex; flex-wrap: nowrap; margin-top:4px">
                <div class="checkbox" style="margin-right:15px"><input onclick="setWanted(this)" name="heads-wanted"
                                                                       id="heads-wanted"
                                                                       type="checkbox" <?php echo $task["headsWanted"] ? "checked" : "" ?>/><label
                            for="heads-wanted">Heads wanted</label></div>
                <div class="checkbox"><input onclick="setWanted(this)" name="help-wanted" id="help-wanted"
                                             type="checkbox" <?php echo $task["helpWanted"] ? "checked" : "" ?>/><label
                            for="help-wanted">Help wanted</label></div>
            </div>
            <h2 id="people">People</h2>
            <h3>Heads</h3>
            <datalist id="headlist">
                <?php
                foreach (getUsers() as $name => $ID) {
                    if (!in_array($ID, $task["heads"])) {
                        echo "<option value='" . $name . "' />";
                    }
                }
                ?>
            </datalist>

            <div style="display:flex; justify-content: space-between">
                <ul id="heads">
                    <li><input type="text" list="headlist" id="invite-head" placeholder="Invite"
                               style="font-size:inherit">
                        <button class="button active" onclick="inviteHead()">Invite</button>
                    </li>
                    <?php

                    foreach ($task["heads"] as $h) {
                        echo "<li>";
                        printName($h);
                        echo "</li>";
                    }

                    ?>
                </ul>
                <ul id="apps" style="margin-right:8%">
                    <li><h4 style="margin: inherit" style="text-align:center">Applications</h4></li>
                    <li style="margin-bottom:5px">Person 1&nbsp
                        <button class="button active">Accept</button>
                        &nbsp
                        <button class="button active">Decline</button>
                    </li>
                    <li style="margin-bottom:5px">Person 1&nbsp
                        <button class="button active">Accept</button>
                        &nbsp
                        <button class="button active">Decline</button>
                    </li>
                    <li style="margin-bottom:5px">Person 1&nbsp
                        <button class="button active">Accept</button>
                        &nbsp
                        <button class="button active">Decline</button>
                    </li>


                </ul>
            </div>

            <button class="button active">Quit head position</button>
        </div>
    </div>

    <script>
        var id = <?php echo $task["ID"] ?>;

        function preview(element) {
            let val = element.value.replace(/\n/g, "<br/>").replace(/<script/g, "&lt;script").replace(/<\/script/g, "&lt;/script");
            element.parentElement.nextElementSibling.innerHTML = val;
        }

        function showValue(element) {
            if (element.previousElementSibling == null) {
                element.nextElementSibling.value = element.value;
            } else {
                element.previousElementSibling.value = element.value;
            }
        }

        let wantTimeout = null;

        function setWanted(source) {
            xhttp.open("POST", "/tasks/backend/modify.php", false);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("task=" + id + "&mode=" + source.name + "&val=" + source.checked);

            console.log(xhttp.responseText);

            if (wantTimeout != null) {
                clearTimeout(wantTimeout);
            }

            wantTimeout = setTimeout(function () {
                xhttp.open("GET", window.location.href, false);
                xhttp.send();
                let text = xhttp.responseText;
                // console.log(text);
                let doc = new DOMParser().parseFromString(text, "text/html");
                console.log(doc);
                console.log(document);
                // console.log(topic);
                let msg = doc.getElementById("wanted");
                let page = document.getElementById("wanted");
                page.innerHTML = msg.innerHTML;
            }, 500);

            xhttp.open("GET", window.location.href, false);
            xhttp.send();
            let text = xhttp.responseText;
            // console.log(text);
            let doc = new DOMParser().parseFromString(text, "text/html");
            console.log(doc);
            console.log(document);
            // console.log(topic);
            let msg = doc.getElementById("name");
            let page = document.getElementById("name");
            page.innerHTML = msg.innerHTML;
        }

        function inviteHead() {
            xhttp.open("POST", "/tasks/backend/modify.php", false);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("task=" + id + "&mode=invite-head"+ "&val=" + document.getElementById("invite-head").value);

            console.log(xhttp.responseText);

            xhttp.open("GET", window.location.href, false);
            xhttp.send();
            let text = xhttp.responseText;
            // console.log(text);
            let doc = new DOMParser().parseFromString(text, "text/html");
            console.log(doc);
            console.log(document);
            // console.log(topic);
            let msg = doc.getElementById("heads");
            let page = document.getElementById("heads");
            console.log(msg);
            console.log(page);
            page.innerHTML = msg.innerHTML;
        }
    </script>

</div>
</body>
