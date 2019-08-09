<body id="task-page">
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/header.php");

include("components/scripts.php");

if (!$task["head"]) {
    echo "<div class='error'>Nothing to see here. Move along.</div>";
    header("Location: ann");
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

    ul{
        list-style-type:none;
        font-size:15px;
        margin-top:0;
    }

    ul .button{
        font-size:70%;
    }

</style>

<?php include("components/top.php") ?>
<div class="below-top">

    <?php include("components/sidebar.php") ?>
    <div class="content">
        <div class="section">
            <h2>General</h2>
            <label>Name <input type="text" name="name" placeholder="The task name" value="<?php echo $task["name"]?>"/></label>
            <?php if($task["parent"] == -1) echo "<!--"?>
            <label style="margin-left:0px; float:right; width:50%">Weight <input oninput="showValue(this)"
                                                                                 class="slider" type="range"
                                                                                 name="weight"
                                                                                 value="<?php echo $task['unassigned'] / 5 ?>"
                                                                                 min="0"
                                                                                 max="<?php echo $task['unassigned'] ?>"/>
                <input oninput="showValue(this)" class="purple" id="value" type="text" value="<?php echo $task['unassigned'] / 5 ?>"
                        maxlength="3"/><span
                        class="purple unit">%</span></label>
            <br/>
            <?php if($task["parent"] == -1) echo "--><br/>"?>
            <br/> <h3 style="width:100%; text-align:center; display:block; margin-bottom: 0px">Subteams</h3>
            <div id="subteams">
                <?php $subs = $task["subteams"]?>
                <select name='team1'>
                    <?php foreach (SUBTEAMS as $team) {
                        $end = $team["ID"]==$subs[0]?"selected":"";
                        echo "<option value='" . $team["ID"]. "' ".$end.">" . $team["name"] . "</option>";
                    } ?>
                </select>

                <select name='team2'>
                    <?php foreach (SUBTEAMS as $team) {
                        $end = $team["ID"]==$subs[1]?"selected":"";
                        echo "<option value='" . $team["ID"] . "' ".$end.">" . $team["name"] . "</option>";
                    } ?>
                </select>

                <select name='team3'>
                    <?php foreach (SUBTEAMS as $team) {
                        $end = $team["ID"]==$subs[2]?"selected":"";
                        echo "<option value='" . $team["ID"] . "' ".$end.">" . $team["name"] . "</option>";
                    } ?>
                </select>
            </div>
            <br/>
            <label>
                Description<br/>
                <textarea id="description" name="desc" rows="15" oninput="preview(this)"><?php echo $task["description"]?></textarea>
            </label>

            <div id="preview">
                <?php echo $task["description"]?>

            </div>
    <br/>
            <button class="button active">Apply</button>

        </div>
        <div class="section">
            <div style="float:right; display:flex; flex-wrap: nowrap; margin-top:4px">
                <div class="checkbox" style="margin-right:15px"><input name="heads-wanted" id="heads-wanted" type="checkbox" "/><label for="heads-wanted">Heads wanted</label></div>
                <div class="checkbox"><input name="help-wanted" id="help-wanted" type="checkbox" "/><label for="help-wanted">Help wanted</label></div>
            </div>
            <h2 id="people">People</h2>
            <h3>Heads</h3>
            <datalist id="headlist">
                <?php
                    foreach (getUsers() as $name=>$ID){
                        if(!in_array($ID, $task["heads"])){
                            echo "<option value='".$name."' />";
                        }
                    }
                ?>
            </datalist>

            <div style="display:flex; justify-content: space-between">
                <ul>
                    <li><input type="text" list="headlist" id="invite-head" placeholder="Invite" style="font-size:inherit">
                        <button class="button active">Invite</button></li>
                    <?php

                    foreach($task["heads"] as $h){
                        echo "<li>";
                        printName($h);
                        echo "</li>";
                    }

                    ?>
                </ul>
                <ul style="font-size:16px; margin-right:8%">
                    <li><h4 style="margin: inherit" style="text-align:center">Applications</h4></li>
                    <li style="margin-bottom:5px">Person 1&nbsp<button class="button active">Accept</button>&nbsp<button class="button active">Decline</button></li>
                    <li style="margin-bottom:5px">Person 1&nbsp<button class="button active">Accept</button>&nbsp<button class="button active">Decline</button></li>
                    <li style="margin-bottom:5px">Person 1&nbsp<button class="button active">Accept</button>&nbsp<button class="button active">Decline</button></li>


                </ul>
            </div>

            <button class="button active" >Quit head position</button>
        </div>
    </div>

    <script>
        function preview(element) {
            console.log("update");
            let val = element.value.replace(/\n/g, "<br/>").replace(/<script/g, "&lt;script").replace(/<\/script/g, "&lt;/script");
            console.log(val);
            element.parentElement.nextElementSibling.innerHTML = val;
            console.log(element.parentElement.nextElementSibling);
        }

        function showValue(element) {
            if (element.previousElementSibling == null) {
                element.nextElementSibling.value = element.value;
            } else {
                element.previousElementSibling.value = element.value;
            }
        }
    </script>

</div>
</body>
