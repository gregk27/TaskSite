<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/include.php");

//Check login status
if (USER["ID"] != -1 && $_POST["mode"] != "edit") {
    echo "ERROR{Already logged in}";
    exit();
}

if ($_POST["mode"] == "register") {
    $name = cleanString($_POST["name"]);
    //Check username
    $stmt = $conn->prepare("SELECT name FROM tasks.users WHERE UPPER(name) like UPPER(?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows != 0) {
        echo "ERROR{Username in use}";
        exit();
    }
    $stmt->close();

    $mail = cleanString($_POST["email"]);
    //Check username
    $stmt = $conn->prepare("SELECT name FROM tasks.users WHERE email = ?");
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows != 0) {
        echo "ERROR{Email in use}";
        exit();
    }
    $stmt->close();

    //Prepare rookie year
    $rookie = cleanString($_POST["rookie"]);

    //Generate ID
    $stmt = $conn->prepare("SELECT name FROM tasks.users WHERE ID = ?");
    $stmt->bind_param("i", $ID);
    $stmt->store_result();
    while (true) {
        $ID = mt_rand(0, 999999);
        $stmt->execute();

        $conflicts = $stmt->get_result()->num_rows;
        if ($conflicts == 0) {
            break;
        }
    }
    $stmt->close();

    $pass = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Add the users to the database
    $stmt = $conn->prepare("INSERT INTO tasks.users (name, email, password, ID, rookie) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $name, $mail, $pass, $ID, $rookie);
    $stmt->execute();
    // Change the mode so the login process will run
    $_POST["mode"] = "login";
}

if ($_POST["mode"] == "login") {
    // If the user is signing in
    // Get users with same name/pass
    $stmt = $conn->prepare("SELECT ID,password FROM tasks.users WHERE email LIKE ?");

//    echo "Email{".$_POST["email"]."}";
//    echo "Password{".$_POST["password"]."}";

    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (isset($user["ID"]) && password_verify($_POST["password"], $user["password"])) {
        //Generate value for token
        $stmt = $conn->prepare("SELECT value FROM tasks.tokens WHERE value = ?");
        $stmt->bind_param("i", $val);
        $stmt->store_result();
        while (true) {
            $val = mt_rand(0, 999999);
            $stmt->execute();

            $conflicts = $stmt->get_result()->num_rows;
            if ($conflicts == 0) {
                break;
            }
        }
        echo "Value: $val";
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO tasks.tokens(user, value, ip) VALUES (?,?,?)");
        $ip = getIP();
        $stmt->bind_param("iis", $user["ID"], $val, $ip);
        $stmt->execute();

        // Set a cookie based on result
        setcookie("token", $val, time() + 12000000, "/");
        echo("Login successful");
        exit();
    } else {
        echo("ERROR{Invalid email or password}");
    }
} else if($_POST["mode"] == "edit" && VALID){
    foreach($_POST as $key=>$val){
        echo $key.",".$val."<br/>";
    }

    $sql = "UPDATE tasks.users SET ";
    $args=array();
    array_push($args, "");
    if(isset($_POST["name"])){
        $sql.="name=?,";
        $args[0].="s";
        array_push($args, cleanString($_POST["name"]));
    }
    if(isset($_POST["email"])){
        $sql.="email=?,";
        $args[0].="s";
        array_push($args, cleanString($_POST["email"]));
    }
    if(isset($_POST["password"])){
        $sql.="password=?,";
        $args[0].="s";
        array_push($args, password_hash($_POST["password"], PASSWORD_DEFAULT));
    }
    if(isset($_POST["rookie"])){
        $sql.="rookie=?,";
        $args[0].="i";
        array_push($args, cleanString($_POST["rookie"]));
    }
    $sql = rtrim($sql, ",")." WHERE ID = ?";
    $args[0].="i";
    array_push($args, USER["ID"]);

    echo $sql."<br/>";
    echo implode(",", $args);


    $stmt = $conn->prepare($sql);
    call_user_func_array(array($stmt, "bind_param"), refValues($args));
    $stmt->execute();
}