<div class="user" id="form" onkeydown="validate()" onkeyup="validate()" autocomplete="off">
    <h2 id='title'>Settings</h2>
    <nav>
        <a class="underline" id="pro-button" onclick="changePage('pro')">Profile</a>
        <a id="acc-button" onclick="changePage('acc')">Account</a>
        <a id="not-button" onclick="changePage('not')">Notifications</a>
    </nav>
    <div id="pro" style="display:block">
        <label>Rookie Year:&nbsp<input name='rookie' type="number" id="rookie" value="<?php echo USER["rookie"] ?>"
                                    oninput="isChanged(this)"/></label><br/>
    </div>
    <div id="acc" style="display:none">
        <label>Username:&nbsp<input name='name' type="text" id="name" value="<?php echo USER["name"] ?>"
                                    oninput="isChanged(this)"/></label><br/>
        <label>Email:&nbsp&nbsp&nbsp&nbsp<input name='email' type="email" id="email"
                                                value="<?php echo USER["email"] ?>"
                                                oninput="isChanged(this);checkEmail()"/></label><br/>
        <br/> <br/>
        <label>Password:&nbsp<input oninput="isChanged(this);confirmPass()" id="pass" name='password' type='password'
                                    placeholder="••••••••" value=""/></label>
        <br/>
        <label>Confirm:&nbsp&nbsp<input oninput="isChanged(this);confirmPass()" id="confirm" type='password'
                                        placeholder="••••••••" value=""/></label><br/>
    </div>

    <div id="not" style="display:none">
        <h3>Notifications</h3>
    </div>

    <span id="error"></span>
    <div class='buttons'>
        <button class="button active" id='submit' onclick="process()">Apply</button>
        <button class="button active" id='reset' onclick="reset()">Reset</button>
    </div>
</div>

<script>
    let active = "pro";

    function changePage(id) {
        document.getElementById(active + "-button").classList.remove("underline");
        document.getElementById(active).style.display = "none";

        document.getElementById(id + "-button").classList.add("underline");
        document.getElementById(id).style.display = "block";

        active = id;
    }

    let errBox = document.getElementById("error");

    function setErr(err) {
        if (err == "") {
            errBox.style.color = "transparent";
        } else {
            errBox.innerHTML = err;
            errBox.style.color = "inherit";
        }
    }

    function isChanged(element) {
        if (element.value != element.placeholder && element.value != "") {
            element.parentElement.classList.add("changed");
        } else {
            element.parentElement.classList.remove("changed");
        }
    }

    function checkEmail() {
        if (/^\S+@\S+\..{2,3}$/g.test(document.getElementById("email").value)) {
            return true;
        } else {
            return false;
        }
    }

    function confirmPass() {
        if (document.getElementById("pass").value != "" || document.getElementById("confirm").value != "") {
            if (document.getElementById("pass").value != document.getElementById("confirm").value) {
                setErr("Passwords do not match");
                return false;
            } else {
                setErr("");
                return true;
            }
        } else {
            setErr("");
            return true;
        }
    }

    function validate() {
        let valid = true;
        if (!checkEmail()) valid = false;
        if (!confirmPass()) valid = false;
        valid ? enableSubmit() : disableSubmit();
        return valid;
    }

    function disableSubmit() {
        document.getElementById("submit").disabled = true;
    }

    function enableSubmit() {
        document.getElementById("submit").disabled = false;
    }

    function reset() {
        let inputs = document.getElementsByTagName("input");

        for (let input of inputs) {
            input.value = input.defaultValue
            if (input.defaultValue != "") input.placeholder = input.defaultValue;
            isChanged(input);
        }

    }

    reset();


    function process(){
        if(!validate())
            return;

        xhttp.open("POST", "/users/local/process.php", false);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        let string = "";
        for (let change of document.querySelectorAll(".changed>input")) {
            if (change.name == "") continue;
            console.log(change);
            string += change.name + "=" + change.value + "&";
        }
        string += "mode=edit";
        xhttp.send(string);
        string = ""; //Clear the string to reduce XSS risk

        window.location = "/user";
    }
</script>
