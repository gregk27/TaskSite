<form method="GET" id="filter">
    <?php
    //Special case for subtasks page
    if (isset($_GET["task"])) {
        echo "<input type='hidden' name='task' value='" . $_GET["task"] . "'/>";
    }

    ?>

    <h3>Filter</h3> <input type="submit" class="button active" value="Apply"
                           style="float:right; margin-top:-35px;"/>

    <div class="checkbox"><input name="subtasks" id="subtasks" type="checkbox"/><label for="subtasks">Include
            subtasks</label></div>
    <?php

    echo '<div class="checkbox"><input name="filter-subteam" id="subteams" type="checkbox" onclick="toggle(this)"/><label for="subteams">By subteam</label></div>';
    echo '<div class="enableSet" id="subteams">';
    foreach (SUBTEAMS as $sub) {
        if ($sub["ID"] == 0 || $sub["isChild"]) continue;
        echo '<div class="checkset"><div class="checkbox parent"><input name="team-' . $sub["ID"] . '" id="check-' . $sub["ID"] . '" type="checkbox" onclick="onChange(this)"/><label for="check-' . $sub["ID"] . '">' . $sub["name"] . '</label></div>';
        foreach (array_filter(explode(",", $sub["children"])) as $cID) {
            $child = SUBTEAMS[$cID];
            echo '<div class="checkbox child"><input name="team-' . $child["ID"] . '" id="check-' . $child["ID"] . '" type="checkbox" onclick="onChange(this)"/><label for="check-' . $child["ID"] . '">' . $child["name"] . '</label></div>';
        }
        echo '</div>';
    }
    echo "</div>";
    ?>

    <div class="checkbox"><input name="filter-prog" id="filter-prog" type="checkbox"
                                 onclick="toggle(this)"/><label for="filter-prog">By progress</label></div>
    <div class="enableSet" id="prog" style="width:175px">
        <div class="checkbox"><input name="use-min-prog" id="use-min-prog" type="checkbox"/><label
                    for="use-min-prog">Min progress</label></div>
        <label style="width:100%">
            <input oninput="showValue(this)" class="slider" type="range" name="min-prog" value="20" min="0"
                   max="99" step="5"/>
            <input oninput="showValue(this)" class="purple" id="value" type="text" value="20" maxlength="2"/>
            <span class="purple unit" style="margin-left:-22px">%</span>
        </label>

        <div class="checkbox"><input name="use-max-prog" id="use-max-prog" type="checkbox"/><label
                    for="use-max-prog">Max progress</label></div>

        <label>
            <input oninput="showValue(this)" class="slider" type="range" name="max-prog" value="20" min="0"
                   max="99" step="5"/>
            <input oninput="showValue(this)" class="purple" id="value" type="number" value="20" maxlength="2"/>
            <span class="purple unit" style="margin-left:-22px">%</span>
        </label>
    </div>

    <?php if (!VALID) echo "<!--" ?>
    <div class="checkbox"><input name="filter-role" id="filter-role" type="checkbox"
                                 onclick="toggle(this)"/><label for="filter-role">My role</label></div>
    <div class="enableSet" id="role" style="width:175px">
        <div class="checkbox"><input name="role-head" id="role-head" type="checkbox"/><label
                    for="role-head">Head</label></div>

        <div class="checkbox"><input name="role-joined" id="role-joined" type="checkbox"/><label
                    for="role-joined">Contributor</label></div>
        <div class="checkbox"><input name="role-follow" id="role-follow" type="checkbox"/><label
                    for="role-follow">Following</label></div>
    </div>
    <?php if (!VALID) echo "-->" ?>

    <div class="checkbox"><input name="filter-demand" id="filter-demand" type="checkbox"
                                 onclick="toggle(this)"/><label for="filter-demand">By Demand</label></div>
    <div class="enableSet" id="demand" style="width:175px">
        <div class="checkbox"><input name="demand-head" id="demand-head" type="checkbox"/><label
                    for="demand-head">Heads Wanted</label></div>

        <div class="checkbox"><input name="demand-help" id="demand-help" type="checkbox"/><label
                    for="demand-help">Help Wanted</label></div>
    </div>

</form>

<script>
    let parameters = location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (let i = 0; i < parameters.length; i++) {
        let name = parameters[i].split("=")[0];
        let targets = document.getElementsByName(name);
        let value = parameters[i].split("=")[1];
        //If it's a button
        if (value == "on") {
            targets[0].checked = false;
            targets[0].click();
        } else {
            for (let i = 0; i < targets.length; i++) {
                console.log(targets[i]);
                targets[i].value = value;
            }
        }
    }

    //Set slider labels
    let sliders = document.getElementsByClassName("slider");
    for (let i = 0; i < sliders.length; i++) {
        sliders[i].nextElementSibling.value = sliders[i].value;
    }
</script>