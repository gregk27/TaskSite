<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/include.php");

//Check login status
if (USER["ID"] != -1) {
    echo "ERROR{Already logged in}";
    exit();
}

if ($_POST["mode"] == "register") {
    $name = $_POST["name"];
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

    $mail = $_POST["email"];
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

    //Generate ID
    $stmt = $conn->prepare("SELECT name FROM tasks.users WHERE ID = ?");
    $stmt->bind_param("i", $ID);
    $stmt->store_result();
    while (true) {
        $ID = mt_rand(0, 25400);
        $stmt->execute();

        $conflicts = $stmt->get_result()->num_rows;
        if ($conflicts == 0) {
            break;
        }
    }
    $stmt->close();

    $pass = $_POST["password"];

    // Add the users to the database
    $stmt = $conn->prepare("INSERT INTO tasks.users (name, email, password, ID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $mail, $pass, $ID);
    $stmt->execute();
    // Set the cookie
    echo setcookie("token", $ID, time() + 12000000, "/");
    header("Location: /");
    echo "Login successful";
    exit();
} else if ($_POST["mode"] == "login") {
    // If the user is signing in
    // Get users with same name/pass
    $stmt = $conn->prepare("SELECT `ID` FROM `tasks`.`users` WHERE `name`= ? AND `password`= ?");
    $stmt->bind_param("ss", $_POST['name'], $_POST['password']);
    $stmt->execute();
    $users = $stmt->get_result();
    if (mysqli_num_rows($users) == 1) {
        // Set a cookie based on result
        setcookie("token", $users->fetch_assoc()["ID"], time() + 12000000, "/");
        header("Location: /");
        exit();
    } else {
        $err = "Invalid name/password";
    }
} else {
    $err = "Mode failed";
}

?>